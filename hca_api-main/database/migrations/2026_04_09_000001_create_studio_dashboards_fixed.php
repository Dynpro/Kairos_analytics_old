<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudioDashboardsFixed extends Migration
{
    public function up()
    {
        // Only create if not exists
        if (!Schema::hasTable('studio_dashboards')) {
            Schema::create('studio_dashboards', function (Blueprint $table) {
                $table->bigIncrements('id');
                
                // Hierarchy fields (matching current looker_data structure)
                $table->string('client_primary_id', 255)->nullable();
                $table->string('client_id', 255)->nullable()->index();
                $table->string('client_name', 255)->nullable();
                $table->string('folder_id', 255)->nullable()->index();
                $table->string('folder_name', 255)->nullable();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('subcategory_id')->nullable()->index();
                $table->string('dash_id', 255)->nullable()->index();
                $table->string('title', 500);
                
                // Looker Studio specific fields
                $table->text('embed_url');
                $table->string('report_id', 255)->nullable();
                $table->json('embed_params')->nullable();
                
                // Access control
                $table->string('access_level', 50)->default('viewer');
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                
                $table->timestamps();
                
                // Composite indexes
                $table->index(['category_id', 'subcategory_id', 'client_id']);
                $table->index(['client_id', 'folder_id']);
            });
        }
        
        // Only create if not exists
        if (!Schema::hasTable('studio_dashboard_user_mapping')) {
            Schema::create('studio_dashboard_user_mapping', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('studio_dashboard_id');
                $table->unsignedBigInteger('grp_usr_mapping_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Short unique index name (under 64 chars)
                $table->unique(['studio_dashboard_id', 'grp_usr_mapping_id'], 'sd_user_map_unique');
                $table->foreign('studio_dashboard_id')
                      ->references('id')
                      ->on('studio_dashboards')
                      ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('studio_dashboard_user_mapping');
        Schema::dropIfExists('studio_dashboards');
    }
}
