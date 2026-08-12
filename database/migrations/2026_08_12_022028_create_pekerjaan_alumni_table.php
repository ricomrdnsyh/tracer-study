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
            $table->string('nama_perusahaan')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->string('posisi_jabatan')->nullable();
            $table->string('skala_perusahaan')->nullable();
            $table->date('tgl_mulai_kerja')->nullable();
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
