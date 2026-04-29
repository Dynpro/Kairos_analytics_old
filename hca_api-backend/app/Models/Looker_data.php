<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Looker_data extends Model
{
    
    protected $table = 'looker_data';
    protected $primaryKey = 'tbl_id';
    protected $fillable = [ 'client_primary_id','client_id','client_name','folder_id','folder_name','category_id','subcategory_id','dash_id','title','created_at','updated_at'];
}
