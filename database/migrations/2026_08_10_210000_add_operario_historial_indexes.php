<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->index(['empresa_id', 'user_id', 'created_at'], 'registros_operativos_historial_idx');
        });

        Schema::table('vacunaciones', function (Blueprint $table) {
            $table->index(['empresa_id', 'user_id', 'created_at'], 'vacunaciones_historial_idx');
        });
    }

    public function down(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->dropIndex('registros_operativos_historial_idx');
        });

        Schema::table('vacunaciones', function (Blueprint $table) {
            $table->dropIndex('vacunaciones_historial_idx');
        });
    }
};
