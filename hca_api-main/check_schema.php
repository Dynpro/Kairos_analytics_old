<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cfm = \Illuminate\Support\Facades\DB::table('client_folder_mapping')->whereIn('folder_id', [5451, 9901])->get();
echo "Client mappings:\n";
foreach($cfm as $c) {
    echo $c->folder_id . " - " . $c->folder_name . " - " . $c->schema_name . "\n";
}
