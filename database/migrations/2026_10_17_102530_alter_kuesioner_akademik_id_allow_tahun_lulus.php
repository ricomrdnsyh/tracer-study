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
            $table->dropForeign('kuesioner_akademik_id_foreign');
            $table->string('akademik_id', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->char('akademik_id', 5)->nullable()->change();
            $table->foreign('akademik_id')->references('id_smt')->on('tahun_akademik');
        });
    }
};
