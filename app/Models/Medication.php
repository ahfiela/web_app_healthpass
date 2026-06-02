<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model (opsional jika nama tabelnya jamak/plural).
     *
     * @var string
     */
    protected $table = 'medications';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment).
     * Berisi field yang sesuai dengan rancangan tabel Master Obat kamu.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_rs',
        'name',  // Nama formula obat standar WHO
        'type',  // Golongan / Khasiat klinis obat
        'stock', // Jumlah stok awal di apotek RS
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     * Mengubah field stock menjadi integer murni di sisi aplikasi.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock' => 'integer',
    ];
}