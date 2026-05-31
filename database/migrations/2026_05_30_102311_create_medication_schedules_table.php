<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('no_bpjs', 13);
            $table->string('medicine_name');
            $table->string('rules'); 
            $table->time('remind_at'); 
            $table->date('start_date');
            $table->date('end_date');
            $table->json('days_of_week')->nullable(); 
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medication_schedules'); }
};