<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StudioDashboard;

class StudioDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing data
        DB::table('studio_dashboards')->truncate();
        
        // Sample data structure for Looker Studio dashboards
        // You can replace this with your actual dashboard data
        $dashboards = [
            // Format: category_id, subcategory_id, client info, folder info, dashboard info, embed_url
            [
                'client_primary_id' => 'CLIENT001',
                'client_id' => 'C001',
                'client_name' => 'Sample Client A',
                'folder_id' => 'F001',
                'folder_name' => 'Overview',
                'category_id' => 1,
                'subcategory_id' => null,
                'dash_id' => 'D001',
                'title' => 'Executive Summary',
                'embed_url' => 'https://lookerstudio.google.com/embed/reporting/YOUR_REPORT_ID/page/xyz',
                'report_id' => 'YOUR_REPORT_ID',
                'access_level' => 'viewer',
                'sort_order' => 1,
            ],
            [
                'client_primary_id' => 'CLIENT001',
                'client_id' => 'C001',
                'client_name' => 'Sample Client A',
                'folder_id' => 'F001',
                'folder_name' => 'Overview',
                'category_id' => 1,
                'subcategory_id' => null,
                'dash_id' => 'D002',
                'title' => 'Monthly Metrics',
                'embed_url' => 'https://lookerstudio.google.com/embed/reporting/YOUR_REPORT_ID_2/page/abc',
                'report_id' => 'YOUR_REPORT_ID_2',
                'access_level' => 'viewer',
                'sort_order' => 2,
            ],
        ];
        
        foreach ($dashboards as $dashboard) {
            StudioDashboard::create($dashboard);
        }
        
        $this->command->info('Studio dashboards seeded successfully!');
        $this->command->info('Total dashboards: ' . count($dashboards));
    }
    
    /**
     * Import dashboards from a JSON file or array
     * 
     * @param array $dashboards Array of dashboard data
     * @return int Number of dashboards imported
     */
    public function importFromArray(array $dashboards): int
    {
        $count = 0;
        foreach ($dashboards as $dashboard) {
            StudioDashboard::updateOrCreate(
                [
                    'client_id' => $dashboard['client_id'] ?? null,
                    'dash_id' => $dashboard['dash_id'] ?? null,
                ],
                $dashboard
            );
            $count++;
        }
        return $count;
    }
    
    /**
     * Migrate from looker_data table
     * This copies structure from existing Looker data but you'll need to add embed URLs manually
     */
    public function migrateFromLookerData()
    {
        $lookerData = DB::table('looker_data')->get();
        
        $count = 0;
        foreach ($lookerData as $row) {
            StudioDashboard::create([
                'client_primary_id' => $row->client_primary_id,
                'client_id' => $row->client_id,
                'client_name' => $row->client_name,
                'folder_id' => $row->folder_id,
                'folder_name' => $row->folder_name,
                'category_id' => $row->category_id,
                'subcategory_id' => $row->subcategory_id,
                'dash_id' => $row->dash_id,
                'title' => $row->title,
                'embed_url' => '', // You'll need to populate this
                'report_id' => '', // You'll need to populate this
                'is_active' => true,
            ]);
            $count++;
        }
        
        $this->command->info("Migrated {$count} records from looker_data");
        $this->command->warn('Note: You need to manually update embed_url and report_id fields');
    }
}
