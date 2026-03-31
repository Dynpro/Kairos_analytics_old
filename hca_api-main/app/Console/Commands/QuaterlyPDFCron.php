<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Looker;
use App\Models\Report;
use App\Models\Report_look;
use App\Http\Requests;
use PHPMailer\PHPMailer;
use PDF;
use DB;
use File;

class QuaterlyPDFCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'QuaterlyPDF:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ReportData= \DB::table('report')
        ->select('report.*')
        ->whereNull('report.file_path')
        ->where(['report.looks_generated' => 2])
        ->where(['report.frequency' => 4])
        ->where(['report.is_active' => 1])
        ->get();

        if(!empty($ReportData)){
            foreach($ReportData as $keys =>$values)
            {
                $id =   $values->report_id;
                $name = $values->name;
                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 3]);
                
                $lookerSetting = Looker::find('1');
                $api_url = $lookerSetting->api_url;
                $client_id = $lookerSetting->client_id;
                $client_secret = $lookerSetting->client_secret;

                $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
                $method = "POST";
                $resp= $this->curlCall($url, $method);
                $responseData = json_decode($resp,true);
                //call to lookers folder api
                $query = array('access_token' => $responseData['access_token']);


                $SectionData= \DB::table('sections')
                        ->select('sections.*')
                        ->join('phm','sections.phm_id','=','phm.id')
                        ->join('report','phm.client_id','=','report.phm_folder_id')
                        ->where(['report.report_id' => $id])
                        ->where(['phm.is_active' => 1])
                        ->orderBy('section_no')
                        ->get();
                 $phmData= \DB::table('phm')
                        ->select('phm.name')
                        ->join('report','phm.client_id','=','report.phm_folder_id')
                        ->where(['report.report_id' => $id])
                        ->where(['phm.is_active' => 1])
                        ->get();

                $SubSecData= DB::select("SELECT
                sub_sections.id,
                sub_sections.sub_section_title,
                sub_sections.sub_section_text,
                sub_sections.sub_section_no,
                sub_sections.section_id,
                sub_sections.phm_id,
                sub_sections.long_table,
                sections.section_no,
                report_look.look_url,
                report_look.look_id,
                report_look.chart_type,
                report_look.embed_url
                FROM
                report INNER JOIN report_look ON
                report.report_id = report_look.report_id
                INNER JOIN sub_sections on report_look.sub_section_id = sub_sections.id
                INNER JOIN sections on sub_sections.section_id = sections.id
                WHERE report.report_id = $id
                ORDER by sections.section_no ASC, sub_sections.sub_section_no ASC");
                
                $SubSectionData = [];
               
                foreach($SubSecData as $key => $value)
                  {
                    $SubSectionData[$value->section_id][$key]['section_id'] =$value->section_id;
                    $SubSectionData[$value->section_id][$key]['sub_section_title'] =$value->sub_section_title;
                    $SubSectionData[$value->section_id][$key]['sub_section_text'] =$value->sub_section_text;
                    $SubSectionData[$value->section_id][$key]['sub_section_no'] =$value->sub_section_no;
                    $SubSectionData[$value->section_id][$key]['look_id'] =$value->look_id;
                    $SubSectionData[$value->section_id][$key]['sub_section_id'] =$value->id;
                    $SubSectionData[$value->section_id][$key]['chart_type'] =$value->chart_type;
                    $SubSectionData[$value->section_id][$key]['embed_url'] =$value->embed_url;
                    $SubSectionData[$value->section_id][$key]['long_look'] =$value->long_table;
                    if(isset($value->look_url) && $value->look_url != ""){
                        if($value->long_table == 0){
                                    try {
                                          $img= imagecreatefrompng($value->look_url); // Load and instantiate the image
                                          if($img) {
                                            $cropped=imagecropauto($img,IMG_CROP_WHITE);
                                            if($cropped !== false){
                                                imagedestroy($img);
                                                ob_start();
                                                imagepng($cropped);
                                                $image = ob_get_contents();
                                                $imgname = $value->look_id.'.png';
                                                $filePath = 'phm_look/' . $imgname;
                                                Storage::disk('s3')->put($filePath, $image); 
                                                ob_end_clean();

                                                $s3 = \Storage::disk('s3');
                                                $client = $s3->getDriver()->getAdapter()->getClient();
                                                $expiry = "+10 minutes";
                                                $imgGetPath = 'phm_look/'.$value->look_id.".png";
                                                $command = $client->getCommand('GetObject', [
                                                  'Bucket' => 'kairos-next-gen-storage', // bucket name
                                                  'Key'    => $imgGetPath
                                                ]);

                                                $request = $client->createPresignedRequest($command, $expiry);
                                                $imagepath =  (string) $request->getUri(); 
                                                $SubSectionData[$value->section_id][$key]['look_img_url'] =$imagepath;
                                              }
                                              else
                                              {
                                                  $SubSectionData[$value->section_id][$keys]['look_img_url'] = "";
                                              }
                                          }
                                      }
                                      catch(Exception $e) {
                                        DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);
                                            echo 'Message: ' .$e->getMessage();                                  
                                    }
                                  
                              }
                              else
                                {
                                $url4 = $api_url . "looks/".$value->look_id."/run/html?apply_formatting=true"; 
                                $method1 = "GET";
                                $htmlData= $this->curlCall($url4, $method1,$query);
                                $SubSectionData[$value->section_id][$key]['look_img_url'] =$htmlData;
                                }
                    
                        }
                    
                  }



                DB::table('report')->where('report_id', $id)->update(['looks_generated' => 4]);
                // return view('reports.view',compact('SectionData','SubSectionData','phmData','id'));
                $this->generate_pdf($name,$id,$SectionData,$SubSectionData,$phmData);
            }
        }
    }
    public function generate_pdf($name,$id,$SectionData,$SubSectionData,$phmData){
        ini_set('max_execution_time', 2400);
        ini_set("pcre.backtrack_limit", "10000000");
        $ReportData= \DB::table('report')
        ->select('report.year','report.phm_folder_id','client_folder_mapping.folder_name','client_folder_mapping.phm_logo','users.email','users.name','report.reporting_year')
        ->join('phm','report.phm_folder_id','=','phm.client_id')
        ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
        ->join('users','report.user_id','=','users.id')
        ->where(['report.report_id' => $id])
        ->get();

        $SchemaData= \DB::table('client_folder_mapping')
        ->select('client_folder_mapping.schema_name')
        ->where(['client_folder_mapping.folder_id' => $ReportData[0]->phm_folder_id])
        ->get();
        $years = rtrim($ReportData[0]->year, ',');
        $date_range=$this->get_dates($years,$SchemaData[0]->schema_name,$ReportData[0]->reporting_year);
     
        if(!empty($date_range))
        {
            $date_range_data = json_decode($date_range);
            try {            
                $pdf = PDF::loadView('reports.view_quaterly',compact('SectionData','SubSectionData','phmData','id','ReportData','date_range_data'));

                $path = 'public/pdf/'.$name.'_'.time().'.pdf';
                Storage::put($path, $pdf->output());

                $filePath = 'Generated_PHM/' . $name.'_'.date('mdy').'.pdf';
                Storage::disk('s3')->put($filePath, $pdf->output());

                $this->send_notification($ReportData[0]->name,$ReportData[0]->email,$path);

                //Remove from Local Storage
                unlink(storage_path('app/'.$path));

                DB::table('report')->where('report_id', $id)->update(['file_path' => $filePath,'looks_generated' => 6]);
            }
            catch(Exception $e) {
                  echo 'Message: ' .$e->getMessage();  
                   DB::table('report')->where('report_id', $id)->update(['looks_generated' => 7]);                                
            }
        }
    }
    public function curlCall($url, $method, $query=null){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            // CURLOPT_HTTPHEADER => array(
            //  "Content-Type: application/x-www-form-urlencoded"
            // ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //echo $responseData['access_token'];
        return $response;
    }

    public function curlCall1($url, $method, $authorization,$query=null){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => array(
             "Content-Type: application/json",
             $authorization
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        
        //echo $responseData['access_token'];
        return $response;
    }
    public function get_dates($years,$schema,$reporting_year)
    {        

        if($reporting_year == "Service")
        {
            if($schema == "SCH_AHC_UPSON_REGIONAL" || $schema == "SCH_AHC_DESOTO_MEMORIAL")
            {       
            $query ="WITH MED AS(SELECT MAX(DIAGNOSIS_DATE) MAX_DATE_MED,MIN(DIAGNOSIS_DATE) MIN_DATE_MED,1 AS ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(DIAGNOSIS_DATE)  IN(".$years."))SELECT MED.* FROM MED";
            }
            else
            {
            $query ="WITH MED AS(SELECT MAX(DIAGNOSIS_DATE) MAX_DATE_MED,MIN(DIAGNOSIS_DATE) MIN_DATE_MED,1 AS MED_ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(DIAGNOSIS_DATE)  IN(".$years.")),PHARMA AS(SELECT MAX(DATE_FILLED) MAX_DATE_PHARMA,MIN(DATE_FILLED) MIN_DATE_PHARMA,1 AS PHR_ID FROM STG_TAB_PHARMACY_DATA WHERE YEAR(DATE_FILLED) IN(".$years."))SELECT MED.*,PHARMA.* FROM MED LEFT JOIN PHARMA ON MED.MED_ID=PHARMA.PHR_ID";
            }
        }
        else
        {
            if($schema == "SCH_AHC_UPSON_REGIONAL" || $schema == "SCH_AHC_DESOTO_MEMORIAL")
            {       
            $query ="WITH MED AS(SELECT MAX(PAID_DATE) MAX_DATE_MED,MIN(PAID_DATE) MIN_DATE_MED,1 AS ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(PAID_DATE)  IN(".$years."))SELECT MED.* FROM MED";
            }
            else
            {
            $query ="WITH MED AS(SELECT MAX(PAID_DATE) MAX_DATE_MED,MIN(PAID_DATE) MIN_DATE_MED,1 AS MED_ID FROM STG_TAB_MEDICAL_DATA WHERE YEAR(PAID_DATE)  IN(".$years.")),PHARMA AS(SELECT MAX(PAID_DATE) MAX_DATE_PHARMA,MIN(PAID_DATE) MIN_DATE_PHARMA,1 AS PHR_ID FROM STG_TAB_PHARMACY_DATA WHERE YEAR(PAID_DATE) IN(".$years."))SELECT MED.*,PHARMA.* FROM MED LEFT JOIN PHARMA ON MED.MED_ID=PHARMA.PHR_ID";
            }
        }
        
        $curl = curl_init(); 

        curl_setopt_array($curl, array(
          CURLOPT_URL => env('SNOWFLAKE_API_URL'),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "query":"'.$query.'",
            "schema":"'.$schema.'"
            }',
          CURLOPT_HTTPHEADER => array(
            'x-api-key: '.env('SNOWFLAKE_API_KEY').'',
            'Content-Type: application/json'
          ),
        ));         

        $response = curl_exec($curl);        

        curl_close($curl);
        return $response;

    }
    public function send_notification($first_name,$email,$file)
    {
        $text             = 'Hi '.$first_name.','."<br/>"."<br/>";
        $text             = $text.'Your scheduled PHM Report Generated successfully.'."<br/>";
        $text             = $text.'Kindly login to  <a href="https://hca.kairosrp.com/">Kairos App</a>'."to download it.<br/>";
        $text             = $text.'Thank You,'."<br/>";
        $text             = $text.'Team Kairos';
        // echo $text;
        // exit();
        $mail             = new PHPMailer\PHPMailer(); // create a n
        $mail->IsSMTP();
        // $mail->SMTPDebug  = 1; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth   = true; // authentication enabled
        $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host       = "email-smtp.us-east-1.amazonaws.com";
        $mail->Port       = 587; ; // or 587
        $mail->IsHTML(true);
        $mail->Username = env("AWS_SMTP_USERNAME");
        $mail->Password = env("AWS_SMTP_PASSWORD");
        $mail->SetFrom("hca@kairosrp.com", 'Kairos App');
        $mail->Subject = "Report Generated Successfully";
        $mail->Body    = $text;
        $mail->AddAttachment(storage_path('app/'.$file));
        $mail->AddAddress($email);
        $check=1;
        // $mail->Send();
        if ($mail->Send()) {
        
            return true;
        }
        else{
            return false;
        }
    }
}
