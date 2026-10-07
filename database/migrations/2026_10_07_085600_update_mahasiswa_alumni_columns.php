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
            $table->renameColumn('tahun_keluar', 'akademik_id');
            $table->dropColumn([
                'tempat_lahir',
                'tanggal_lahir',
                'ipk',
                'sks_ketuntasan',
                'semester',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->renameColumn('akademik_id', 'tahun_keluar');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('ipk', 10)->nullable();
            $table->integer('sks_ketuntasan')->nullable();
            $table->integer('semester')->nullable();
        });
    }
};
