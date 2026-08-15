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
        Schema::create('pekerjaan_alumni', function (Blueprint $table) {
            $table->id('id_pekerjaan');
            $table->unsignedBigInteger('respon_id');
            $table->foreign('respon_id')->references('id_respon')->on('respon_tracer')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nama')->nullable();
            $table->string('nama_normalized')->nullable();
            $table->string('jenis_instansi')->nullable();
            $table->string('kode_provinsi')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_kabupaten')->nullable();
            $table->string('kabupaten')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pekerjaan_alumni');
    }
};
