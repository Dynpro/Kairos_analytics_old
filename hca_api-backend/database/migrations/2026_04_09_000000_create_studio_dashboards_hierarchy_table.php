<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudioDashboardsHierarchyTable extends Migration
{
    public function up()
    {
        // Drop existing table if exists (fresh start for Looker Studio migration)
        Schema::dropIfExists('studio_dashboards');
        
        Schema::create('studio_dashboards', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Hierarchy fields (mirroring looker_data structure)
            $table->string('client_primary_id', 100)->nullable()->index();
            $table->string('client_id', 100)->nullable()->index();
            $table->string('client_name', 255)->nullable()->index();
            $table->string('folder_id', 100)->nullable()->index();
            $table->string('folder_name', 255)->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
            
            // Dashboard identification
            $table->string('dash_id', 100)->nullable()->index();
            $table->string('title', 255);
            
            // Looker Studio specific fields
            $table->text('embed_url');           // Base embed URL
            $table->string('report_id', 255)->nullable();  // Looker Studio report ID
            $table->json('embed_params')->nullable();      // Additional embed parameters
            
            // Access control (similar to current user access system)
            $table->string('access_level', 50)->default('viewer'); // viewer, explorer
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            
            // Audit fields
            $table->timestamps();
            
            // Composite indexes for common queries
            $table->index(['category_id', 'subcategory_id', 'client_id']);
            $table->index(['client_id', 'folder_id']);
        });
        
        // Create user access mapping table for Looker Studio dashboards
        Schema::create('studio_dashboard_user_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('studio_dashboard_id');
            $table->unsignedBigInteger('grp_usr_mapping_id'); // Links to grp_role_usr_mapping
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['studio_dashboard_id', 'grp_usr_mapping_id'], 'sd_user_map_unique');
            $table->foreign('studio_dashboard_id')
                  ->references('id')
                  ->on('studio_dashboards')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('studio_dashboard_user_mapping');
        Schema::dropIfExists('studio_dashboards');
    }
}
