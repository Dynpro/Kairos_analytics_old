<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client_Category extends Model
{
    protected $table = 'client_category';
    protected $fillable = [ 'category_id', 'category', 'created_at', 'updated_at'];
}
