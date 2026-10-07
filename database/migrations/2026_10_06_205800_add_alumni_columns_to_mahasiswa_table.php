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
            $table->string('tahun_keluar', 10)->nullable()->after('prodi_id');
            $table->char('jenis_kelamin', 1)->nullable()->after('nama');
            $table->string('tempat_lahir', 100)->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->tinyInteger('id_jenis_keluar')->nullable()->after('status');
            $table->string('ipk', 10)->nullable()->after('id_jenis_keluar');
            $table->integer('sks_ketuntasan')->nullable()->after('ipk');
            $table->integer('semester')->nullable()->after('sks_ketuntasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn([
                'tahun_keluar',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'id_jenis_keluar',
                'ipk',
                'sks_ketuntasan',
                'semester',
            ]);
        });
    }
};
