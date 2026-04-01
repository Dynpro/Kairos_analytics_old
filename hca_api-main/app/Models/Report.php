<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    protected $table = 'report';
    protected $primaryKey = 'report_id';
    protected $fillable = [ 'name', 'year', 'user_id', 'phm_folder_id', 'reporting_year', 'schedule_time','file_path','created_by','looks_generated','storeLook_folder_id','frequency'];
}
