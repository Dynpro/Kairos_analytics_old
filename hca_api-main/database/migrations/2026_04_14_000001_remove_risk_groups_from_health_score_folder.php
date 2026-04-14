<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveRiskGroupsFromHealthScoreFolder extends Migration
{
    public function up()
    {
        DB::table('studio_dashboards')
            ->where('folder_name', 'Health Score & Predictive Reports')
            ->whereIn('title', [
                'Risk Groups Stratification Overview',
                'Risk Groups (Member-Level Analysis)',
                'Risk Groups Migration (Detailed Analysis)',
            ])
            ->delete();
    }

    public function down()
    {
        // Re-insert if rolled back
        $rows = [
            [
                'client_primary_id' => 'DEMO',
                'client_id'         => 'DEMO001',
                'client_name'       => 'Demo Client',
                'folder_id'         => 'F013',
                'folder_name'       => 'Health Score & Predictive Reports',
                'category_id'       => 1,
                'subcategory_id'    => null,
                'dash_id'           => 'D079',
                'title'             => 'Risk Groups Stratification Overview',
                'embed_url'         => 'https://lookerstudio.google.com/embed/reporting/73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF',
                'report_id'         => '73c33d5c-576f-47fe-845b-892af7a851da',
                'access_level'      => 'viewer',
                'sort_order'        => 10,
                'is_active'         => true,
            ],
            [
                'client_primary_id' => 'DEMO',
                'client_id'         => 'DEMO001',
                'client_name'       => 'Demo Client',
                'folder_id'         => 'F013',
                'folder_name'       => 'Health Score & Predictive Reports',
                'category_id'       => 1,
                'subcategory_id'    => null,
                'dash_id'           => 'D080',
                'title'             => 'Risk Groups (Member-Level Analysis)',
                'embed_url'         => 'https://lookerstudio.google.com/embed/reporting/ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF',
                'report_id'         => 'ae9843b1-4d25-4119-8038-9acf1c1ab235',
                'access_level'      => 'viewer',
                'sort_order'        => 11,
                'is_active'         => true,
            ],
            [
                'client_primary_id' => 'DEMO',
                'client_id'         => 'DEMO001',
                'client_name'       => 'Demo Client',
                'folder_id'         => 'F013',
                'folder_name'       => 'Health Score & Predictive Reports',
                'category_id'       => 1,
                'subcategory_id'    => null,
                'dash_id'           => 'D081',
                'title'             => 'Risk Groups Migration (Detailed Analysis)',
                'embed_url'         => 'https://lookerstudio.google.com/embed/reporting/3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF',
                'report_id'         => '3e1b27d4-53b4-4b82-8259-b56c03a41cd2',
                'access_level'      => 'viewer',
                'sort_order'        => 12,
                'is_active'         => true,
            ],
        ];

        DB::table('studio_dashboards')->insert($rows);
    }
}
