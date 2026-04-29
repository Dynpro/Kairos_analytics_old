<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateMskMrsModifiableUrl extends Migration
{
    public function up()
    {
        DB::table('studio_dashboards')
            ->where('title', 'MRS Modifiable ICD Codes - Overall Analysis')
            ->update([
                'embed_url' => 'https://lookerstudio.google.com/reporting/90f0e070-9b11-4034-8344-513e30d7ccd3/page/tF7RSver',
            ]);
    }

    public function down()
    {
        DB::table('studio_dashboards')
            ->where('title', 'MRS Modifiable ICD Codes - Overall Analysis')
            ->update([
                'embed_url' => 'https://lookerstudio.google.com/reporting/90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF',
            ]);
    }
}
