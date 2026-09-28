<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_aves', function (Blueprint $table) {
            $table->string('idempotencia_clave', 64)->nullable()->after('metadata');

            $table->unique(['empresa_id', 'idempotencia_clave'], 'movimientos_aves_empresa_idempotencia_unique');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_aves', function (Blueprint $table) {
            $table->dropUnique('movimientos_aves_empresa_idempotencia_unique');
            $table->dropColumn('idempotencia_clave');
        });
    }
};
