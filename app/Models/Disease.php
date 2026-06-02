<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disease extends Model 
{
    // REVISI: Ubah dari $guarded menjadi $fillable agar data diizinkan masuk ke database
    protected $fillable = [
        'icd_code', 
        'name', 
        'description', 
        'is_critical'
    ];
}