<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$user_id = 13; // We'll just print for all or for a specific user

echo "users_folder_access:\n";
$ufa = DB::table('users_folder_access')->get();
foreach($ufa as $u) { echo json_encode($u) . "\n"; }

echo "\nclient_folder_mapping:\n";
$cfm = DB::table('client_folder_mapping')->get();
foreach($cfm as $c) { echo json_encode($c) . "\n"; }

