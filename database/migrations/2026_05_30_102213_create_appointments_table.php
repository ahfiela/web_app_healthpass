<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('no_bpjs', 13);
            $table->string('rs_name');
            $table->date('appointment_date');
            $table->string('notes');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('appointments'); }
};