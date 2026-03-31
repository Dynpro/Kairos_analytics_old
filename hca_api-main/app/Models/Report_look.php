<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report_look extends Model
{
    protected $table = 'report_look';
    protected $primaryKey = 'id';
    protected $fillable = [ 'report_id', 'section_id', 'sub_section_id', 'sub_section_no','chart_type','look_id','look_url','embed_url'];
}
