<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class HospitalAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'hospital_admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'kode_rs',
        'nama_rs',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}