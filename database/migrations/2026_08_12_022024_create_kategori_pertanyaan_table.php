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
        Schema::create('kategori_pertanyaan', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->unsignedBigInteger('kuesioner_id');
            $table->foreign('kuesioner_id')->references('id_kuesioner')->on('kuesioner')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nama_kategori');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_pertanyaan');
    }
};
