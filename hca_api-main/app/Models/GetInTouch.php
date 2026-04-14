<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GetInTouch extends Model
{
    protected $table = 'get_in_touch';
    protected $primaryKey = 'id';
    protected $fillable = [ 'id', 'name', 'email', 'contact', 'subject', 'message'];
}
