<?php

// Test endpoint to verify frontend data
Route::get('/api/test-frontend-data', function () {
    $dashboards = DB::table('studio_dashboards')
        ->where('is_active', 1)
        ->orderBy('id')
        ->take(5)
        ->get(['id', 'dash_id', 'title']);
    
    return response()->json([
        'message' => 'Frontend should see these IDs',
        'dashboards' => $dashboards,
        'timestamp' => time(),
        'cache_headers' => 'no-cache'
    ])
    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
    ->header('Pragma', 'no-cache')
    ->header('Expires', '0');
});
