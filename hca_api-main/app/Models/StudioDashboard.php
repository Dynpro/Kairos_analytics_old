<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioDashboard extends Model
{
    protected $table = 'studio_dashboards';

    protected $fillable = [
        'client_primary_id',
        'client_id',
        'client_name',
        'folder_id',
        'folder_name',
        'category_id',
        'subcategory_id',
        'dash_id',
        'title',
        'embed_url',
        'report_id',
        'access_level',
        'sort_order',
        'is_active',
    ];
}

