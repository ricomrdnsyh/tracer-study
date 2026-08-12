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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->char('nim', 10)->primary();
            $table->string('prodi_id', 36);
            $table->foreign('prodi_id')->references('id_prodi')->on('prodi')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('angkatan');
            $table->date('tgl_lulus')->nullable();
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
