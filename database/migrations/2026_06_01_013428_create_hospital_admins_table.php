<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_admins', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rs'); // Terhubung ke server validasi pusat
            $table->string('nama_rs'); // Diambil otomatis dari pusat
            $table->string('name');    // Nama admin petugas
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_admins');
    }
};