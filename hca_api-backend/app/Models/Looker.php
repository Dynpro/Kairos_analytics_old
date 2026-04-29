<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Looker extends Model
{
    protected $table = 'looker';
    protected $fillable = [ 'api_url', 'client_id', 'client_secret', 'secret', 'host'];
}
