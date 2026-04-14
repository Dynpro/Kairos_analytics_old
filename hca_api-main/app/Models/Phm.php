<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Phm extends Model
{
    protected $table = 'phm';
    protected $primaryKey = 'id';
    protected $fillable = [ 'name', 'client_id', 'client_secret', 'pdf_path', 'chart1', 'chart2','text1','text2','start_date','report_type','end_date','created_by','pharma_start_date','pharma_end_date','entity_id','updade_by'];
}
