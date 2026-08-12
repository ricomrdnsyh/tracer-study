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
        Schema::create('prodi', function (Blueprint $table) {
            $table->string('id_prodi', 36)->primary();
            $table->unsignedTinyInteger('fakultas_id');
            $table->foreign('fakultas_id')->references('id_fakultas')->on('fakultas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nama_prodi', 100);
            $table->string('singkatan', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
