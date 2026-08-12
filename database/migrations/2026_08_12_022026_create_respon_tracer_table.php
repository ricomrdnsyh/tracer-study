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
        Schema::create('respon_tracer', function (Blueprint $table) {
            $table->id('id_respon');
            $table->char('mahasiswa_id', 10);
            $table->foreign('mahasiswa_id')->references('nim')->on('mahasiswa')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('kuesioner_id');
            $table->foreign('kuesioner_id')->references('id_kuesioner')->on('kuesioner')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('status');
            $table->dateTime('tgl_isi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respon_tracer');
    }
};
