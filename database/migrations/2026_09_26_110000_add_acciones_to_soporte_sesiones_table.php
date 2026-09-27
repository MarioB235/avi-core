<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soporte_sesiones', function (Blueprint $table) {
            $table->json('acciones')->nullable()->after('end_reason');
        });
    }

    public function down(): void
    {
        Schema::table('soporte_sesiones', function (Blueprint $table) {
            $table->dropColumn('acciones');
        });
    }
};
