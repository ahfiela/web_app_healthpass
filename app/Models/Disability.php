<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disability extends Model
{
    use HasFactory;

    // Menentukan kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Relasi ke model User (Many-to-Many)
     * Menghubungkan kekurangan ini dengan pasien-pasien yang memilikinya
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_disability');
    }
}