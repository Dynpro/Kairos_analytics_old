<?php
require 'bootstrap/app.php';
use Illuminate\Support\Facades\DB;
try {
    $dashboards = DB::table('looker_data')->select('title', 'dash_id')->get();
    echo json_encode($dashboards, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    file_put_contents('php://stderr', $e->getMessage());
    exit(1);
}
