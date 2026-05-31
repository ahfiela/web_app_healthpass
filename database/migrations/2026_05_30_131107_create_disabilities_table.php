<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Kelainan / Kekurangan Fisik Pasien
        Schema::create('disabilities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: KW-01, KW-02
            $table->string('name'); // Contoh: Buta Warna, Mata Minus (>2)
            $table->timestamps();
        });

        // 2. Tabel Pivot Hubungan Pasien dengan Kelainan yang Dimiliki
        Schema::create('user_disability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('disability_id')->constrained('disabilities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_disability');
        Schema::dropIfExists('disabilities');
    }
};