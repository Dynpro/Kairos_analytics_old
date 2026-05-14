<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Simulate exactly what PHMAutomationController@index does
$user_id = 1; // Assuming the user is 1, maybe they are someone else?
$entity_id = env('env_entity_id', 1);

$ReportData = DB::table('report')
    ->select('report.*','client_folder_mapping.folder_name')
    ->join('phm','report.phm_folder_id','=','phm.client_id')
    ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
    ->where([
        'report.is_active' => '1',
        'phm.is_active' => '1',
        'client_folder_mapping.is_active' => '1',
        'phm.entity_id' => $entity_id,
        'report.user_id' => $user_id
    ])
    ->distinct()
    ->get();

echo "User 1 count: " . count($ReportData) . "\n";

$user_id = 269; // Let's check other users? Or just fetch the latest inserted report user ID.
$latest = DB::table('report')->orderBy('report_id', 'desc')->first();
echo "Latest report ID: " . $latest->report_id . " Name: " . $latest->name . " user_id: " . $latest->user_id . "\n";

$ReportDataLatestUser = DB::table('report')
    ->select('report.*','client_folder_mapping.folder_name')
    ->join('phm','report.phm_folder_id','=','phm.client_id')
    ->join('client_folder_mapping','phm.client_id','=','client_folder_mapping.folder_id')
    ->where([
        'report.is_active' => '1',
        'phm.is_active' => '1',
        'client_folder_mapping.is_active' => '1',
        'phm.entity_id' => $entity_id,
        'report.user_id' => $latest->user_id
    ])
    ->distinct()
    ->get();

echo "User " . $latest->user_id . " count: " . count($ReportDataLatestUser) . "\n";

$found = false;
foreach ($ReportDataLatestUser as $r) {
    if ($r->report_id == $latest->report_id) {
        $found = true;
    }
}
echo "Latest report found in API list for user? " . ($found ? "YES" : "NO") . "\n";

