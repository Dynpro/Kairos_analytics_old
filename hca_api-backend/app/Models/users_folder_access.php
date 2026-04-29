<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class users_folder_access extends Model
{
    protected $table = 'users_folder_access';
    protected $fillable = [ 'user_id', 'category_id','subcategory_id','folder_id','folder_primary_id'];
}
