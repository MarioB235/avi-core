<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->unsignedInteger('huevos_descarte')->nullable()->after('huevos');
        });
    }

    public function down(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->dropColumn('huevos_descarte');
        });
    }
};
