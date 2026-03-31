<?php

namespace App\Console\Commands;
use App\Models\Looker;
use App\Models\Looker_parent_dashboards;
use Illuminate\Console\Command;

class ParentDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ParentDashboard:cron';

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
        $lookerSetting = \DB::table('looker')
        ->select('*')
        ->limit(1)
        ->get();
        $api_url = $lookerSetting[0]->api_url;
        $client_id = $lookerSetting[0]->client_id;
        $client_secret = $lookerSetting[0]->client_secret;
        
        $url = $api_url . "login?client_id=".$client_id."&client_secret=".$client_secret;
        $method = "POST";
        $resp= $this->curlCall($url, $method);
        $responseData = json_decode($resp,true);
        //echo $responseData['access_token'];

        //call to lookers folder api
        $query = array('access_token' => $responseData['access_token']);
        //$url1 = "https://dynpro.cloud.looker.com:443/api/3.1/folders";
        //$url1 = $api_url."folders";
        $url1 = $api_url . "folders/319/children";
        $method1 = "GET";
        $folders= $this->curlCall($url1, $method1,$query);
        $folders= json_decode($folders, true);
        $folderData = array();
        $folderDataArr = array();
        foreach ($folders as $folder){
            $folderData['id']= $folder['id'];
            $folderData['name']= $folder['name'];
            $folderDataArr[] = $folderData;
        }
        // print_r($folderDataArr);

        Looker_parent_dashboards::truncate();
        Looker_parent_dashboards::insert($folderDataArr);
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
