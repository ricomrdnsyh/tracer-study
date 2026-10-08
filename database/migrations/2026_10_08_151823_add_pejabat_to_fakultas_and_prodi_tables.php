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
        Schema::table('fakultas', function (Blueprint $table) {
            $table->string('nama_dekan')->nullable()->after('nama_fakultas');
        });

        Schema::table('prodi', function (Blueprint $table) {
            $table->string('jenjang')->nullable()->after('nama_prodi');
            $table->string('nama_kaprodi')->nullable()->after('jenjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fakultas', function (Blueprint $table) {
            $table->dropColumn('nama_dekan');
        });

        Schema::table('prodi', function (Blueprint $table) {
            $table->dropColumn(['jenjang', 'nama_kaprodi']);
        });
    }
};
