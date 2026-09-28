<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->boolean('cero_confirmado')->default(false)->after('idempotencia_clave');
        });
    }

    public function down(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->dropColumn('cero_confirmado');
        });
    }
};
