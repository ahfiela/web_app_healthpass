<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens;
    protected $guarded = [];
    protected $hidden = ['password'];

    // TAMBAHAN RELASI: Memudahkan penarikan seluruh riwayat rekam medis pasien
    public function medicalRecords() {
        return $this->hasMany(MedicalRecord::class, 'no_bpjs', 'no_bpjs');
    }

    public function disabilities()
    {
        return $this->belongsToMany(Disability::class, 'user_disability');
    }
}