<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserActivityLog extends Model
{ 
    protected $table = 'user_activity_logs';
    protected $primaryKey = 'id';
    protected $fillable = [ 'user_id', 'ip', 'browser', 'country', 'state','city'];
}
