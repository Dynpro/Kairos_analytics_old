<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudioDashboardsTable extends Migration
{
    public function up()
    {
        Schema::create('studio_dashboards', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Hierarchy fields (keep aligned with frontend nav expectations)
            $table->string('folder', 255)->nullable()->index();
            $table->string('title', 255);

            // Looker Studio embed/report URL
            $table->text('iframe_url');

            // Optional ordering + soft toggle
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('studio_dashboards');
    }
}

