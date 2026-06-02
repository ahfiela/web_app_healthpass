<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('doctors', function (Blueprint $table) { $table->string('kode_rs')->after('id'); });
    Schema::table('rooms', function (Blueprint $table) { $table->string('kode_rs')->after('id'); });
    Schema::table('medications', function (Blueprint $table) { $table->string('kode_rs')->after('id'); });
    Schema::table('diseases', function (Blueprint $table) { $table->string('kode_rs')->after('id')->nullable(); });
    Schema::table('medical_records', function (Blueprint $table) { $table->string('kode_rs')->after('id'); });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tables', function (Blueprint $table) {
            //
        });
    }
};
