<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client_SubCategory extends Model
{
    protected $table = 'client_subcategory';
    protected $fillable = [ 'subcategory_id', 'subcategory', 'created_at', 'updated_at'];
}
