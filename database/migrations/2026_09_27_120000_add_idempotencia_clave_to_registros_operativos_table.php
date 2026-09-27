<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->string('idempotencia_clave', 64)->nullable()->after('tipo');

            $table->unique(['empresa_id', 'idempotencia_clave'], 'registros_operativos_empresa_idempotencia_unique');
        });
    }

    public function down(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->dropUnique('registros_operativos_empresa_idempotencia_unique');
            $table->dropColumn('idempotencia_clave');
        });
    }
};
