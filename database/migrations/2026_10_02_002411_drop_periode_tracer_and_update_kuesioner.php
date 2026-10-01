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
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropColumn('periode_id');
            $table->date('tgl_mulai')->nullable()->after('id_kuesioner');
            $table->date('tgl_selesai')->nullable()->after('tgl_mulai');
        });

        Schema::dropIfExists('periode_tracer');
    }

    public function down(): void
    {
        Schema::create('periode_tracer', function (Blueprint $table) {
            $table->id('id_periode');
            $table->string('nama_periode');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->string('status');
            $table->timestamps();
        });

        Schema::table('kuesioner', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('id_kuesioner');
            $table->foreign('periode_id')->references('id_periode')->on('periode_tracer')->restrictOnDelete()->cascadeOnUpdate();
            $table->dropColumn(['tgl_mulai', 'tgl_selesai']);
        });
    }
};
