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
        Schema::dropIfExists('tahun_akademik');

        Schema::create('tahun_akademik', function (Blueprint $table) {
            $table->string('id_smt', 10)->primary();
            $table->string('nm_smt', 100);
            $table->char('aktif', 1)->default('t');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahun_akademik');
    }
};
