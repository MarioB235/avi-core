<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galpones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('granja_id')->constrained('granjas')->restrictOnDelete();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->unsignedInteger('capacidad')->nullable();
            $table->string('estado')->default('activo');
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('aves_actuales')->default(0);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('granja_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galpones');
    }
};
