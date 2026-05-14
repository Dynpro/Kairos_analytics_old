<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$user_id = 13; // We'll just print for all or for a specific user

$reportData = \DB::table('report')
    ->select('report.*','client_folder_mapping.folder_name')
    ->join('phm','report.phm_folder_id','=','phm.client_id')
    ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
    ->where([
        'report.is_active' => '1',
        'phm.is_active' => '1',
        'client_folder_mapping.is_active' => '1',
        'phm.entity_id' => env('env_entity_id', 1)
    ])
    ->distinct()
    ->get();

echo "INDEX QUERY RESULTS:\n";
foreach($reportData as $r) {
    echo $r->report_id . " | " . $r->name . " | " . $r->phm_folder_id . " | " . $r->folder_name . " | user_id: " . $r->user_id . "\n";
}

echo "\nALL REPORTS FOR 9901:\n";
$all = DB::table('report')->where('phm_folder_id', 9901)->get();
foreach($all as $r) {
    echo $r->report_id . " | " . $r->name . " | " . $r->phm_folder_id . " | is_active: " . $r->is_active . " | user_id: " . $r->user_id . "\n";
}

echo "\nALL DEMO REPORTS:\n";
$all = DB::table('report')->where('phm_folder_id', '!=', 9901)->where('phm_folder_id', '!=', 9902)->limit(5)->get();
foreach($all as $r) {
    echo $r->report_id . " | " . $r->name . " | " . $r->phm_folder_id . " | is_active: " . $r->is_active . " | user_id: " . $r->user_id . "\n";
}
