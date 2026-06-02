<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();

            $table->string('no_bpjs', 13)->unique();

            $table->string('blood_type')->nullable();

            $table->float('height_cm')->nullable();
            $table->float('weight_kg')->nullable();

            Schema::create('user_disease', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('disease_id')->constrained('diseases')->onDelete('cascade');
    $table->string('status')->default('perlu_pemantauan'); // Contoh: 'perlu_pemantauan', 'darurat', 'stabil'
    $table->string('notes')->nullable(); // Contoh: "Stadium 2" atau catatan dokter
    $table->timestamps();
});
            $table->text('drug_allergies')->nullable();
            $table->text('food_allergies')->nullable();

            $table->text('operation_history')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            $table->enum(
                'health_status',
                [
                    'sehat',
                    'perlu_pemantauan',
                    'darurat'
                ]
            )->default('sehat');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profiles');
    }
};