<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
protected $table = 'sections';
protected $primaryKey = 'id';
protected $fillable = [ 
    'section_title',
    'section_text',
    'section_no',
    'phm_id'
    ];
}
