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
        Schema::table('kategori_pertanyaan', function (Blueprint $table) {
            $table->unsignedBigInteger('syarat_pertanyaan_id')->nullable()->after('urutan');
            $table->json('syarat_jawaban')->nullable()->after('syarat_pertanyaan_id');

            $table->foreign('syarat_pertanyaan_id')->references('id_pertanyaan')->on('pertanyaan')->onDelete('set null');
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->unsignedBigInteger('syarat_pertanyaan_id')->nullable()->after('wajib');
            $table->json('syarat_jawaban')->nullable()->after('syarat_pertanyaan_id');

            $table->foreign('syarat_pertanyaan_id')->references('id_pertanyaan')->on('pertanyaan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_pertanyaan', function (Blueprint $table) {
            $table->dropForeign(['syarat_pertanyaan_id']);
            $table->dropColumn(['syarat_pertanyaan_id', 'syarat_jawaban']);
        });

        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->dropForeign(['syarat_pertanyaan_id']);
            $table->dropColumn(['syarat_pertanyaan_id', 'syarat_jawaban']);
        });
    }
};
