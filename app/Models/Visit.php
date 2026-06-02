<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    // Daftarkan kolom agar bisa diisi oleh data dari Flutter
    protected $fillable = [
        'no_bpjs',
        'kode_rs',
        'rs_name',
        'visit_date',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'no_bpjs', 'no_bpjs');
    }
}