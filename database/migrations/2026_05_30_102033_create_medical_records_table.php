<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->unique()->constrained('visits')->onDelete('cascade');
            $table->string('no_bpjs', 13);
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->foreignId('room_id')->constrained('rooms');
            $table->foreignId('disease_id')->constrained('diseases');
            $table->text('symptoms');
            $table->enum('patient_status', ['sembuh-total', 'rawat-jalan', 'rawat-inap']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medical_records'); }
};