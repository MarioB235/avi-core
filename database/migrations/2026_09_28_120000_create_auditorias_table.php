<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->constrained('users');
            $table->string('categoria', 32);
            $table->string('accion', 64);
            $table->string('entidad_tipo', 120)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->text('motivo')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['empresa_id', 'occurred_at']);
            $table->index(['categoria', 'occurred_at']);
            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
