<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures the phm table has the is_master column needed for the
 * default PHM template feature.
 */
class CreatePhmDefaultSectionsSeederTable extends Migration
{
    public function up()
    {
        Schema::table('phm', function (Blueprint $table) {
            if (!Schema::hasColumn('phm', 'is_master')) {
                $table->tinyInteger('is_master')->default(0)->after('entity_id')
                    ->comment('1 = master/default template, 0 = client-specific');
            }
            if (!Schema::hasColumn('phm', 'file_path')) {
                $table->text('file_path')->nullable()->after('is_master')
                    ->comment('Uploaded formatted copy path on S3');
            }
        });
    }

    public function down()
    {
        Schema::table('phm', function (Blueprint $table) {
            foreach (['is_master', 'file_path'] as $col) {
                if (Schema::hasColumn('phm', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
