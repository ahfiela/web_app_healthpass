<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model {
    protected $guarded = [];

    public function visit() { return $this->belongsTo(Visit::class); }
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function disease() { return $this->belongsTo(Disease::class); }

    // TAMBAHAN RELASI: Menghubungkan rekam medis ke user lewat nomor BPJS
    public function user() { 
        return $this->belongsTo(User::class, 'no_bpjs', 'no_bpjs'); 
    }
}