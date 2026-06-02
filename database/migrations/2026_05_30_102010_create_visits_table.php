<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('visits', function (Blueprint $table) {
    $table->id();
    $table->string('no_bpjs', 13);
    $table->string('kode_rs'); // 🟢 Tambahkan baris ini
    $table->string('rs_name');
    $table->date('visit_date');
    $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
    $table->timestamps();
});
    }
    public function down(): void { Schema::dropIfExists('visits'); }
};