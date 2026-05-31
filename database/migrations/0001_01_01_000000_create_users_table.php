<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_bpjs', 13)->unique();
            $table->date('born');
            $table->enum('gender', ['male', 'female']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};