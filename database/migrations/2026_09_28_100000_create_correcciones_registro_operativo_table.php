<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correcciones_registro_operativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registro_operativo_id')->constrained('registros_operativos')->cascadeOnDelete();
            $table->json('valores_anteriores');
            $table->json('valores_nuevos');
            $table->text('motivo');
            $table->foreignId('corregido_por')->constrained('users');
            $table->timestamp('fecha_efectiva');
            $table->timestamps();

            $table->index(['empresa_id', 'registro_operativo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correcciones_registro_operativo');
    }
};
