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
        Schema::create('master_negara', function (Blueprint $table) {
            $table->string('kode_wilayah_negara')->primary();
            $table->string('negara')->nullable();
            $table->timestamps();
        });

        Schema::create('master_provinsi', function (Blueprint $table) {
            $table->string('kode_wilayah_provinsi')->primary();
            $table->string('kode_wilayah_negara');
            $table->string('provinsi')->nullable();
            $table->timestamps();

            $table->foreign('kode_wilayah_negara')->references('kode_wilayah_negara')->on('master_negara')->onDelete('cascade');
        });

        Schema::create('master_kota_kabupaten', function (Blueprint $table) {
            $table->string('kode_wilayah_kota_kabupaten')->primary();
            $table->string('kode_wilayah_provinsi');
            $table->string('kota_kabupaten')->nullable();
            $table->timestamps();

            $table->foreign('kode_wilayah_provinsi')->references('kode_wilayah_provinsi')->on('master_provinsi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kota_kabupaten');
        Schema::dropIfExists('master_provinsi');
        Schema::dropIfExists('master_negara');
    }
};
