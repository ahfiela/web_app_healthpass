<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password'
    ];

    public function medicalRecords()
    {
        return $this->hasMany(
            MedicalRecord::class,
            'no_bpjs',
            'no_bpjs'
        );
    }

    public function disabilities()
    {
        return $this->belongsToMany(
            Disability::class,
            'user_disability'
        );
    }

    public function healthProfile()
    {
        return $this->hasOne(
            HealthProfile::class,
            'no_bpjs',
            'no_bpjs'
        );
    }
    public function diseases() {
    return $this->belongsToMany(Disease::class, 'user_disease')->withPivot('status', 'notes');
}
}