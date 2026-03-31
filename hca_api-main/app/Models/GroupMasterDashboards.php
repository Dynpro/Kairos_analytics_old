<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMasterDashboards extends Model
{
    protected $table = 'group_role_dashboards_mapping';
     protected $primaryKey = 'id';
    protected $fillable = [ 'group_id','role_id','category_id','subcategory_id','client_primary_id','client_id','dashboard_id','sub_dashboard_id','is_active','created_by','updated_by'];
}
