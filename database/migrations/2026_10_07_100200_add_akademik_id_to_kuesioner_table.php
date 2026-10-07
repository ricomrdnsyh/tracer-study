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
            $table->string('akademik_id', 10)->nullable()->after('id_kuesioner');
            $table->foreign('akademik_id')
                ->references('id_smt')
                ->on('tahun_akademik')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->dropForeign(['akademik_id']);
            $table->dropColumn('akademik_id');
        });
    }
};
