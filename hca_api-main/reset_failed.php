<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Find and show failed/completed reports that need resetting
$reports = DB::table('report')
    ->where('is_active', 1)
    ->orderBy('report_id', 'desc')
    ->limit(10)
    ->get(['report_id', 'name', 'phm_folder_id', 'looks_generated', 'file_path']);

echo "Latest 10 reports:\n";
foreach($reports as $r) {
    echo "ID: {$r->report_id} | Name: {$r->name} | folder: {$r->phm_folder_id} | looks_generated: {$r->looks_generated} | file_path: {$r->file_path}\n";
}

// Reset the latest failed reports (looks_generated != 0 but file_path is empty = failed)
$reset = DB::table('report')
    ->where('is_active', 1)
    ->where(function($q) {
        $q->whereNull('file_path')->orWhere('file_path', '');
    })
    ->whereNotIn('looks_generated', [0])
    ->update(['looks_generated' => 0, 'file_path' => null]);

echo "\nReset $reset failed reports back to pending.\n";
