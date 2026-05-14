<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$entity_id = env('env_entity_id', 1);

// Get Demo to copy its access mapping if needed, but we'll just insert Apollo and Kairos directly.
$apollo_folder_id = 9901;
$kairos_folder_id = 9902;

DB::table('client_folder_mapping')->updateOrInsert(
    ['folder_id' => $apollo_folder_id],
    [
        'folder_name' => 'Apollo',
        'phm_folder_id' => $apollo_folder_id,
        'parent_folder_id' => 0,
        'schema_name' => 'SCH_TEST_COMPANY_2',
        'is_parent_phm' => 1,
        'is_active' => 1,
        'entity_id' => $entity_id,
        'type' => 'PHM'
    ]
);

DB::table('phm')->updateOrInsert(
    ['client_id' => $apollo_folder_id],
    [
        'is_active' => 1,
        'entity_id' => $entity_id
    ]
);

DB::table('client_folder_mapping')->updateOrInsert(
    ['folder_id' => $kairos_folder_id],
    [
        'folder_name' => 'Kairos',
        'phm_folder_id' => $kairos_folder_id,
        'parent_folder_id' => 0,
        'schema_name' => 'SCH_TEST_COMPANY_2',
        'is_parent_phm' => 1,
        'is_active' => 1,
        'entity_id' => $entity_id,
        'type' => 'PHM'
    ]
);

DB::table('phm')->updateOrInsert(
    ['client_id' => $kairos_folder_id],
    [
        'is_active' => 1,
        'entity_id' => $entity_id
    ]
);

// We should also add these to users_folder_access for the current user.
// The user ID was 13 in a previous log, but we can just give access to all users or the first user.
$user = DB::table('users')->first();
if ($user) {
    // We need the primary ID of the client_folder_mapping row
    $apollo_row = DB::table('client_folder_mapping')->where('folder_id', $apollo_folder_id)->first();
    $kairos_row = DB::table('client_folder_mapping')->where('folder_id', $kairos_folder_id)->first();
    
    if ($apollo_row) {
        DB::table('users_folder_access')->updateOrInsert(
            ['user_id' => $user->id, 'folder_primary_id' => $apollo_row->id],
            []
        );
    }
    if ($kairos_row) {
        DB::table('users_folder_access')->updateOrInsert(
            ['user_id' => $user->id, 'folder_primary_id' => $kairos_row->id],
            []
        );
    }
}

// Update the reports that were already created for 9901 and 9902 so they have the correct client_id
// Just to make sure they show up! (Though if the UI already sent them, they have phm_folder_id 9901 and 9902)
echo "DB Updated.\n";
