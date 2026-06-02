<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // TAMBAHKAN 'kode_rs' ke dalam array ini
    protected $fillable = ['kode_rs', 'nip', 'name', 'specialist'];
}