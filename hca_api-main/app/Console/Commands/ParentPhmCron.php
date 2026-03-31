<?php

namespace App\Console\Commands;
use Illuminate\Console\Command; 
use App\Models\Looker;
use App\Models\Looker_parent_phm;

class ParentPhmCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ParentPhm:cron';

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
        $lookerSetting = Looker::find('1');
        $api_url = $lookerSetting->api_url;
        $client_id = $lookerSetting->client_id;
        $client_secret = $lookerSetting->client_secret;

        $url = $api_url . "login?client_id=" . $client_id . "&client_secret=" . $client_secret;
        $method = "POST";
        $resp = $this->curlCall($url, $method);
        $responseData = json_decode($resp, true);

        // Call to Looker's folder API
        $query = ['access_token' => $responseData['access_token']];
        $method1 = 'GET';
        $folderChildUrl = $api_url . "folders/5450/children";
        $childData = $this->curlCall($folderChildUrl, $method1, $query);
        $childData = json_decode($childData, true);

        $folderChildArr = [];
        foreach ($childData as $fldr) {
            $folderChildArr[] = [
                'id' => $fldr['id'],
                'name' => $fldr['name'],
            ];
        }

        Looker_parent_phm::truncate();
        Looker_parent_phm::insert($folderChildArr);
    }

    /**
     * Perform a CURL call.
     *
     * @param string $url
     * @param string $method
     * @param array|null $query
     * @return mixed
     */
    public function curlCall($url, $method, $query = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $query,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            \Log::error('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        return $response;
    }
}
