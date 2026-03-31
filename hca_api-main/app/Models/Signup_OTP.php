<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Signup_OTP extends Model
{ 
    protected $table = 'signup_otp';
    protected $primaryKey = 'id';
    protected $fillable = [ 'first_name', 'last_name', 'email', 'group_code', 'otp','otp_count','flag'];
}

