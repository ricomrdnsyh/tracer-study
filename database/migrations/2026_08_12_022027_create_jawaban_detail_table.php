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
        Schema::create('jawaban_detail', function (Blueprint $table) {
            $table->id('id_jawaban');
            $table->unsignedBigInteger('respon_id');
            $table->foreign('respon_id')->references('id_respon')->on('respon_tracer')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('pertanyaan_id');
            $table->foreign('pertanyaan_id')->references('id_pertanyaan')->on('pertanyaan')->restrictOnDelete()->cascadeOnUpdate();
            $table->text('jawaban_text')->nullable();
            $table->json('jawaban_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_detail');
    }
};
