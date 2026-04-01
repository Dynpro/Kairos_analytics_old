<?php

namespace App\Console\Commands;
use Illuminate\Console\Command; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Looker;
use App\Models\Report;
use App\Models\Report_look;
use App\Http\Requests;
use PDF;
use DB;
use File;

class WeeklyCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Weekly:cron';

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
        ini_set('max_execution_time', 1500);
        ini_set("pcre.backtrack_limit", "10000000");        
        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;

        $ReportData= \DB::table('report')
        ->select('report.*')
        ->where(['report.looks_generated' => 0])
        ->where(['report.frequency' => 2])
        ->where(['report.is_active' => 1])
        ->get();

        if(isset($ReportData) && !empty($ReportData))
        {
            foreach($ReportData as $keys =>$values)
            {
                if(isset($values->phm_folder_id))
                    {

                    $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
                    $method = "POST";
                    $resp= $this->curlCall($url, $method);
                    $responseData = json_decode($resp,true);
                    //call to lookers folder api
                    $query = array('access_token' => $responseData['access_token']);
             
                    $SectionData= \DB::table('sections')
                    ->select('sections.id','sections.section_title','sections.section_text','sections.section_no')
                    ->join('phm','sections.phm_id','=','phm.id')
                    ->where(['phm.client_id' => $values->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy('sections.id','sections.section_title','sections.section_text','sections.section_no')
                    ->orderBy('section_no')
                    ->get();

                    $SubSecData= \DB::table('sub_sections')
                    ->select('sub_sections.id','sub_sections.sub_section_title','sub_sections.sub_section_text','sub_sections.sub_section_no','sub_sections.section_id','sub_sections.look_id')
                    ->join('sections','sub_sections.section_id','=','sections.id')
                    ->join('phm','sections.phm_id','=','phm.id')
                    ->where(['phm.client_id' => $values->phm_folder_id])
                    ->where(['phm.is_active' => 1])
                    ->groupBy('sub_sections.id','sub_sections.sub_section_title','sub_sections.sub_section_text','sub_sections.sub_section_no','sub_sections.section_id','sub_sections.look_id')
                    ->orderBy('section_id')
                    ->get();

                    
                    $SubSectionData=[];
                    $looks_data=[];
                    $look=[];
                    foreach($SubSecData as $key => $value)
                    {
                        if(isset($value->look_id) && !empty($value->look_id)){                        

                            $url1 = $api_url . "looks/".$value->look_id; 
                            $method1 = "GET";
                            $lookerData= $this->curlCall($url1, $method1,$query); 
                            $lookerData1 = json_decode($lookerData,true);

                            $folder_id = $lookerData1['folder_id'];
                            $model = $lookerData1['model']['id'];

                            $title = $lookerData1['title']."_".$values->report_id."_".$values->user_id."_".$values->year;

                            $view = $lookerData1['query']['view'];

                            $create_look_arr=[
                                "model" =>          $model,
                                "view" =>           $view,
                                "title" =>          $title,
                                "folder_id" =>      $values->storeLook_folder_id,
                                "public" =>         true
                            ];


                            $QueriesJson = $lookerData1['query'];
                            unset($QueriesJson['id']);
                            unset($QueriesJson['can']);
                            unset($QueriesJson['url']);
                            unset($QueriesJson['expanded_share_url']);
                            unset($QueriesJson['share_url']);
                            unset($QueriesJson['client_id']);
                            unset($QueriesJson['slug']);

                            foreach($QueriesJson['filters'] as $k => $val)                
                            {
                                if($k == "vw_medical.diagnosis_date" || $k == "vw_med_and_pharma_summary_1.PAID_YEAR" || $k == "vw_risk_group_migration.File_year" || $k == "vw_medical.Paid_year" || $k == "vw_medication_possession_ratio.year" || $k == "vw_medical.diagnosis_year" || $k == "vw_pharmacy.service_year" || $k == "vw_medical.reporting_year" || $k == "vw_pharmacy.reporting_year" || $k == "hedis_measure.year")
                                {
                                    $QueriesJson['filters'][$k] = "".$values->year."";
                                }
                                if($k == "vw_medical.reporting_date_filter" || $k == "vw_pharmacy.reporting_date_filter")
                                {
                                    $QueriesJson['filters'][$k] = "".$values->reporting_year."";

                                }
                            
                            }

                           

                            $payload = json_encode($QueriesJson);
                            $url2 = $api_url . "queries?fields=id";
                            $authorization = "Authorization: Bearer ".$responseData['access_token']; 
                            $lookerData2= $this->curlCall1($url2, $method, $authorization, $payload); 
                            $lookerData3 = json_decode($lookerData2,true);

                            $create_look_arr['query_id'] = $lookerData3['id'];
                            $create_Look_payload = json_encode($create_look_arr);

                            $url3 = $api_url . "looks";
                            $lookerData4= $this->curlCall1($url3, $method, $authorization, $create_Look_payload); 
                            $lookerData5 = json_decode($lookerData4,true);

                            $SubSectionData[$value->section_id][$key]['section_id'] =$value->section_id;
                            $SubSectionData[$value->section_id][$key]['sub_section_title'] =$value->sub_section_title;
                            $SubSectionData[$value->section_id][$key]['sub_section_text'] =$value->sub_section_text;
                            $SubSectionData[$value->section_id][$key]['sub_section_no'] =$value->sub_section_no;
                            $SubSectionData[$value->section_id][$key]['section_no'] =$value->section_id;
                            $SubSectionData[$value->section_id][$key]['look_id'] =$value->look_id;
                            $SubSectionData[$value->section_id][$key]['look_img_url'] =(isset($lookerData5['image_embed_url']))?$lookerData5['image_embed_url']:"";
                            $SubSectionData[$value->section_id][$key]['embed_url'] = (isset($lookerData5['embed_url']) && !empty($lookerData5['embed_url']))?$lookerData5['embed_url']:"";

                            if(isset($lookerData5['id']) && !empty($lookerData5['id']))
                            {
                            $looks_data['report_id']        = $values->report_id;
                            $looks_data['section_id']       = $value->section_id;
                            $looks_data['sub_section_id']   = $value->id;
                            $looks_data['sub_section_no']   = $value->sub_section_no;
                            $looks_data['look_id']          = $lookerData5['id'];
                            $looks_data['chart_type']       = $lookerData5['query']['vis_config']['type'];
                            $looks_data['embed_url']         = (isset($lookerData5['embed_url']) && !empty($lookerData5['embed_url']))?$lookerData5['embed_url']:"";
                            $looks_data['look_url']         = (isset($lookerData5['image_embed_url']) && !empty($lookerData5['image_embed_url']))?$lookerData5['image_embed_url']:"";
                            Report_look::insert($looks_data);
                            }
                        }
                        else
                        {
                            $looks_data1['report_id']        = $values->report_id;
                            $looks_data1['section_id']       = $value->section_id;
                            $looks_data1['sub_section_id']   = $value->id;
                            $looks_data1['sub_section_no']   = $value->sub_section_no;
                            Report_look::insert($looks_data1);
                        }
                    }                       
                            
                        DB::table('report')->where('report_id', $values->report_id)->update(['looks_generated' => 1]);
                        $id = $values->report_id;
                        // return view('reports.view_look',compact('SectionData','SubSectionData','id'));
                        $pdf = PDF::loadView('reports.view_look_weekly',compact('SectionData','SubSectionData','id'));
                        $path = 'public/pdf/'.$values->name.'_'.time().'.pdf';
                        DB::table('report')->where('report_id', $values->report_id)->update(['looks_generated' => 2]);
                        Storage::put($path, $pdf->output());
                            
                }
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
}
