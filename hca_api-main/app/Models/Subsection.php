<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subsection extends Model
{
protected $table = 'sub_sections';
protected $primaryKey = 'id';
protected $fillable = [ 
    'sub_section_title',
    'sub_section_text',
    'sub_section_no',
    'section_id',
    'look_img_url',
    'phm_id',
    'look_id',
    'look_name',
    'long_table'
    ];
}
