<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Groups extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'group_id';
    protected $fillable = [ 'group_name','entity_id' ,'is_active','created_by'];

}
