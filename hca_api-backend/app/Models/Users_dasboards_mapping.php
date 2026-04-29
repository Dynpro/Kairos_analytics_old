<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Users_dasboards_mapping extends Model
{
    protected $table = 'users_dasboards_mapping';
    protected $primaryKey = 'usr_dash_id';
    protected $fillable = [ 'grp_usr_mapping_id','category_id','subcategory_id','client_primary_id','client_id','dashboard_id','sub_dashboard_id','is_active','created_by'];
}
