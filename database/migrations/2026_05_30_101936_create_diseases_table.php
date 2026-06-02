<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('diseases', function (Blueprint $table) {
    $table->id();
    $table->string('icd_code')->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('is_critical')->default(false); // <--- TAMBAHKAN INI
    $table->timestamps();
});
    }
    public function down(): void { Schema::dropIfExists('diseases'); }
};