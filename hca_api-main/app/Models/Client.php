<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client_folder_mapping';
    protected $primaryKey = 'id';
    protected $fillable = [ 'id','folder_id', 'phm_folder_id','folder_name','category','subcategory','schema_name','is_phm','organization_unique_id','contact_email','owner_unique_id','iss','is_approved','entity_id','type','group_id','external_group_id','models','access_filters','logo'];
}
