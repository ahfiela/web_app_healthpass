<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID Pasien
            $table->string('kode_rs'); // Disimpan kodenya (Contoh: RS-PMI, RSUD-CIAWI)
            $table->string('nama_rs'); // Direkam namanya untuk mempercepat load dashboard
            $table->enum('status', ['Pending', 'Disetujui', 'Selesai'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_requests');
    }
};