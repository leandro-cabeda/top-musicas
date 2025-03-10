<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('musicas', function (Blueprint $table) {
            $table->string('url')->nullable()->after('thumb');
        });

        DB::table('musicas')->update(['url' => '']);

        Schema::table('musicas', function (Blueprint $table) {
            $table->string('url')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('musicas', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};
