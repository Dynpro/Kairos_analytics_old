<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Looker_parent_phm extends Model
{
    protected $table = 'looker_parent_phm';
    protected $primaryKey = 'tbl_id';
    protected $fillable = [ 'id','name','created_at','updated_at'];
}
