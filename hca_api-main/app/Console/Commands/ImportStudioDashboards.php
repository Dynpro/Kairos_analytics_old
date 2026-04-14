<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudioDashboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportStudioDashboards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'studio:import 
                            {--file= : JSON file path containing dashboard data}
                            {--from-looker : Migrate from existing looker_data table}
                            {--dry-run : Show what would be imported without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Looker Studio dashboards from JSON file or migrate from Looker data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $count = 0;
        
        if ($this->option('from-looker')) {
            $count = $this->migrateFromLooker($dryRun);
        } elseif ($this->option('file')) {
            $count = $this->importFromFile($this->option('file'), $dryRun);
        } else {
            $this->error('Please specify either --file or --from-looker option');
            return 1;
        }
        
        if (!$dryRun) {
            $this->info("Successfully imported {$count} dashboards");
        } else {
            $this->info("Would import {$count} dashboards (dry-run mode)");
        }
        
        return 0;
    }
    
    /**
     * Import from JSON file
     */
    private function importFromFile(string $filePath, bool $dryRun): int
    {
        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 0;
        }
        
        $json = File::get($filePath);
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file: ' . json_last_error_msg());
            return 0;
        }
        
        $count = 0;
        foreach ($data as $item) {
            $this->info("Processing: {$item['title']} (Client: {$item['client_name']})");
            
            if (!$dryRun) {
                StudioDashboard::updateOrCreate(
                    [
                        'client_id' => $item['client_id'] ?? null,
                        'dash_id' => $item['dash_id'] ?? null,
                    ],
                    [
                        'client_primary_id' => $item['client_primary_id'] ?? null,
                        'client_name' => $item['client_name'] ?? null,
                        'folder_id' => $item['folder_id'] ?? null,
                        'folder_name' => $item['folder_name'] ?? null,
                        'category_id' => $item['category_id'] ?? null,
                        'subcategory_id' => $item['subcategory_id'] ?? null,
                        'title' => $item['title'],
                        'embed_url' => $item['embed_url'],
                        'report_id' => $item['report_id'] ?? null,
                        'access_level' => $item['access_level'] ?? 'viewer',
                        'sort_order' => $item['sort_order'] ?? 0,
                        'is_active' => $item['is_active'] ?? true,
                    ]
                );
            }
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Migrate from existing looker_data table
     */
    private function migrateFromLooker(bool $dryRun): int
    {
        $lookerData = DB::table('looker_data')->get();
        
        if ($lookerData->isEmpty()) {
            $this->warn('No data found in looker_data table');
            return 0;
        }
        
        $count = 0;
        foreach ($lookerData as $row) {
            $this->info("Processing: {$row->title} (Client: {$row->client_name})");
            
            if (!$dryRun) {
                StudioDashboard::updateOrCreate(
                    [
                        'client_id' => $row->client_id,
                        'dash_id' => $row->dash_id,
                    ],
                    [
                        'client_primary_id' => $row->client_primary_id,
                        'client_name' => $row->client_name,
                        'folder_id' => $row->folder_id,
                        'folder_name' => $row->folder_name,
                        'category_id' => $row->category_id,
                        'subcategory_id' => $row->subcategory_id,
                        'title' => $row->title,
                        'embed_url' => '', // Need to be populated manually
                        'report_id' => '', // Need to be populated manually
                        'is_active' => true,
                    ]
                );
            }
            $count++;
        }
        
        if (!$dryRun) {
            $this->warn('Note: embed_url and report_id fields need to be populated manually');
        }
        
        return $count;
    }
}
