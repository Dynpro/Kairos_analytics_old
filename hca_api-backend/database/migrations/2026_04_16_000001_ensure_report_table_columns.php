<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureReportTableColumns extends Migration
{
    public function up()
    {
        Schema::table('report', function (Blueprint $table) {
            if (!Schema::hasColumn('report', 'is_active')) {
                $table->tinyInteger('is_active')->default(1)->after('frequency');
            }
            if (!Schema::hasColumn('report', 'schema_name')) {
                $table->string('schema_name', 100)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('report', 'medical_start_date')) {
                $table->date('medical_start_date')->nullable();
            }
            if (!Schema::hasColumn('report', 'medical_end_date')) {
                $table->date('medical_end_date')->nullable();
            }
            if (!Schema::hasColumn('report', 'pharmacy_start_date')) {
                $table->date('pharmacy_start_date')->nullable();
            }
            if (!Schema::hasColumn('report', 'pharmacy_end_date')) {
                $table->date('pharmacy_end_date')->nullable();
            }
            if (!Schema::hasColumn('report', 'reporting_year')) {
                $table->string('reporting_year', 20)->nullable();
            }
            if (!Schema::hasColumn('report', 'studio_report_url')) {
                $table->text('studio_report_url')->nullable();
            }
        });

        // Make sure all existing reports are active
        \DB::table('report')->whereNull('is_active')->update(['is_active' => 1]);
    }

    public function down()
    {
        // intentionally left empty — don't drop columns on rollback
    }
}
