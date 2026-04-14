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
        // PHM client list is now sourced directly from studio_dashboards.
        // This cron previously synced Looker folder children into looker_parent_phm,
        // which is no longer queried.  The cron is kept registered but is a no-op
        // to avoid breaking the scheduler.
        \Log::info('ParentPhmCron: no-op (PHM clients now served from studio_dashboards)');
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
