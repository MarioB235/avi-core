<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacunaciones', function (Blueprint $table) {
            $table->string('idempotencia_clave', 64)->nullable()->after('vacuna');

            $table->unique(['empresa_id', 'idempotencia_clave'], 'vacunaciones_empresa_idempotencia_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vacunaciones', function (Blueprint $table) {
            $table->dropUnique('vacunaciones_empresa_idempotencia_unique');
            $table->dropColumn('idempotencia_clave');
        });
    }
};
