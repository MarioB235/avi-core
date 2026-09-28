<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_aves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->string('tipo', 32);
            $table->string('estado', 16)->default('activo');
            $table->foreignId('galpon_origen_id')->nullable()->constrained('galpones')->restrictOnDelete();
            $table->foreignId('galpon_destino_id')->nullable()->constrained('galpones')->restrictOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->integer('ajuste_delta')->nullable();
            $table->text('motivo');
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamp('fecha_efectiva');
            $table->unsignedBigInteger('reversa_de_id')->nullable();
            $table->unsignedBigInteger('reversado_por_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('reversa_de_id')->references('id')->on('movimientos_aves')->restrictOnDelete();
            $table->foreign('reversado_por_id')->references('id')->on('movimientos_aves')->restrictOnDelete();

            $table->index(['empresa_id', 'fecha_efectiva']);
            $table->index(['galpon_origen_id', 'fecha_efectiva']);
            $table->index(['galpon_destino_id', 'fecha_efectiva']);
            $table->index(['lote_id', 'fecha_efectiva']);
            $table->index('estado');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_aves');
    }
};
