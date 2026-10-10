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
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->index('status', 'idx_mahasiswa_status');
        });

        Schema::table('respon_tracer', function (Blueprint $table) {
            $table->index('status', 'idx_respon_tracer_status');
            $table->index('tgl_isi', 'idx_respon_tracer_tgl_isi');
            $table->index(['kuesioner_id', 'status'], 'idx_respon_kuesioner_status');
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->index('kode_pertanyaan', 'idx_pertanyaan_kode');
        });

        Schema::table('jawaban_detail', function (Blueprint $table) {
            $table->index(['respon_id', 'pertanyaan_id'], 'idx_jawaban_respon_pertanyaan');
            $table->index(['pertanyaan_id', 'respon_id'], 'idx_jawaban_pertanyaan_respon');
        });

        Schema::table('pekerjaan_alumni', function (Blueprint $table) {
            $table->index('nama', 'idx_pekerjaan_nama');
            $table->index('provinsi', 'idx_pekerjaan_provinsi');
        });

        Schema::table('kuesioner', function (Blueprint $table) {
            $table->index('status', 'idx_kuesioner_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->dropIndex('idx_kuesioner_status');
        });

        Schema::table('pekerjaan_alumni', function (Blueprint $table) {
            $table->dropIndex('idx_pekerjaan_nama');
            $table->dropIndex('idx_pekerjaan_provinsi');
        });

        Schema::table('jawaban_detail', function (Blueprint $table) {
            $table->dropIndex('idx_jawaban_respon_pertanyaan');
            $table->dropIndex('idx_jawaban_pertanyaan_respon');
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->dropIndex('idx_pertanyaan_kode');
        });

        Schema::table('respon_tracer', function (Blueprint $table) {
            $table->dropIndex('idx_respon_tracer_status');
            $table->dropIndex('idx_respon_tracer_tgl_isi');
            $table->dropIndex('idx_respon_kuesioner_status');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex('idx_mahasiswa_status');
        });
    }
};
