<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Requests;
use DB;

class SessionTimeoutUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SessionTimeoutUsers:cron';

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
        $SessionDetails = DB::select("SELECT account_id,actual_user,flag,updated_at, UTC_TIMESTAMP(), TIMESTAMPDIFF(MINUTE, updated_at,UTC_TIMESTAMP()) AS difference FROM account_master WHERE flag=1");
            if(!empty($SessionDetails[0]))
            {
                foreach($SessionDetails as $key => $value)
                {
                    if($value->difference != "" && $value->difference >29)
                    {
                        \DB::table('account_master')->where('account_id', $value->account_id)->update(['flag' => 0,'actual_user'=>Null]);
                    } 
                }
            }
    }
}
