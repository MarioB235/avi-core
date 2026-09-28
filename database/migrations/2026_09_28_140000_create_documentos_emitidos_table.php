<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_emitidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('emitido_por')->constrained('users')->restrictOnDelete();
            $table->string('tipo', 64);
            $table->string('formato', 16);
            $table->string('nombre_archivo');
            $table->string('storage_disk', 32);
            $table->string('storage_path');
            $table->char('checksum_sha256', 64);
            $table->json('filtros')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('emitido_at');
            $table->timestamp('fecha_corte')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'emitido_at']);
            $table->index(['empresa_id', 'tipo', 'emitido_at']);
            $table->unique(['storage_disk', 'storage_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_emitidos');
    }
};
