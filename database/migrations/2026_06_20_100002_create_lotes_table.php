<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('galpon_id')->constrained('galpones')->cascadeOnDelete();
            $table->string('codigo');
            $table->date('fecha_nacimiento');
            $table->date('fecha_ingreso');
            $table->unsignedInteger('cantidad_inicial');
            $table->string('linea_raza')->nullable();
            $table->string('tipo_huevo')->default('blanco');
            $table->string('estado')->default('activo');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('galpon_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
