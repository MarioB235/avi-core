<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soporte_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('motivo');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason')->nullable();
            $table->timestamps();

            $table->index(['actor_id', 'ended_at']);
            $table->index(['empresa_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soporte_sesiones');
    }
};
