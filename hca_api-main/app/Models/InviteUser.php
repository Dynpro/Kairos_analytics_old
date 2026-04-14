<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteUser extends Model
{  
    protected $table = 'invite_user';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','user_email','group_code','created_by','updated_by'];
}