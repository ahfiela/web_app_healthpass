<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model {
    protected $guarded = [];

    public function medicalRecord() { return $this->hasOne(MedicalRecord::class); }

    // TAMBAHAN RELASI: Menghubungkan log kunjungan ke akun user lewat nomor BPJS
    public function user() { 
        return $this->belongsTo(User::class, 'no_bpjs', 'no_bpjs'); 
    }
}