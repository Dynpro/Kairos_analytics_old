<?php

namespace App\Console\Commands;
use App\Models\Looker;
use App\Models\Looker_data;
use Illuminate\Console\Command;

class LookerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LookerData:cron';

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
        $clients= \DB::table('client_folder_mapping')
        ->select('client_folder_mapping.id','client_folder_mapping.folder_id','client_folder_mapping.folder_name','client_folder_mapping.category','client_folder_mapping.subcategory')
        ->where('client_folder_mapping.is_active',1)
        ->where('client_folder_mapping.type',"Client")
        ->whereNotNull('client_folder_mapping.folder_id')
        ->get();


        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;
        $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
        $method = "POST";
        $method1 = "GET";
        $resp= $this->curlCall($url, $method);
        $responseData = json_decode($resp,true);
        $query = array('access_token' => $responseData['access_token']);
        $mainArr1 = [];
        $mainArr2 = [];
        $mainArr3 = [];
        foreach($clients as $key => $value)
        {
            ///GET Dashboards Based ON Client id
            $folderChildUrl = $api_url . "folders/".$value->folder_id."/children";
            $childData= $this->curlCall($folderChildUrl, $method1,$query);
            $childData= json_decode($childData, true);   
            if(!empty($childData))
            {         
            $SubFolderDashboards = [];
            foreach ($childData as $fldr){                
                    $mainArr1['client_primary_id'] = $value->id;
                    $mainArr1['client_id'] = $value->folder_id;
                    $mainArr1['client_name'] = $value->folder_name;
                    $mainArr1['category_id'] = $value->category;
                    $mainArr1['subcategory_id'] = $value->subcategory;
                    $mainArr1['folder_id'] = $fldr['id'];
                    $mainArr1['folder_name'] = $fldr['name'];
                    foreach ($fldr['dashboards'] as $keyss => $valuesss) {                    
                        $SubFolderDashboards['dash_id'] = $valuesss['id'];
                        $SubFolderDashboards['title'] = $valuesss['title'];
                        $mainArr2 = array_merge($mainArr1,$SubFolderDashboards);
                        array_push($mainArr3,$mainArr2);
                    }
                }
            }
            else
            {
                    $mainArr4['client_primary_id']  = $value->id;
                    $mainArr4['client_id']          = $value->folder_id;
                    $mainArr4['client_name']        = $value->folder_name;
                    $mainArr4['category_id']        = $value->category;
                    $mainArr4['subcategory_id']     = $value->subcategory;
                    $mainArr4['folder_id']          = Null;
                    $mainArr4['folder_name']        = Null;
                    $mainArr4['dash_id']            = Null;
                    $mainArr4['title']              = Null;
                    array_push($mainArr3,$mainArr4);
            }
        }

        Looker_data::truncate();
        Looker_data::insert($mainArr3);
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
}
