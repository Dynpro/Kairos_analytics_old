<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScreenshotUrlToStudioDashboards extends Migration
{
    public function up()
    {
        Schema::table('studio_dashboards', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_dashboards', 'screenshot_url')) {
                $table->text('screenshot_url')->nullable()->after('embed_url')
                    ->comment('Static screenshot/thumbnail URL used in PDF generation instead of a text link');
            }
        });
    }

    public function down()
    {
        Schema::table('studio_dashboards', function (Blueprint $table) {
            $table->dropColumn('screenshot_url');
        });
    }
}
