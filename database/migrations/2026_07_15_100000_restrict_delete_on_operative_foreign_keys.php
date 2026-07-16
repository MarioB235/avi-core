<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anulación lógica: no borrar en cascada historial operativo ni estructura avícola.
 * FK padres → RESTRICT (inactivar/anular en aplicación; no hard-delete).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rebindForeign('vacunaciones', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
            'lote_id' => 'lotes',
            'user_id' => 'users',
        ], 'restrict');

        $this->rebindForeign('registros_operativos', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
            'user_id' => 'users',
        ], 'restrict');

        $this->rebindForeign('lotes', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
        ], 'restrict');

        $this->rebindForeign('galpones', [
            'empresa_id' => 'empresas',
            'granja_id' => 'granjas',
        ], 'restrict');

        $this->rebindForeign('granjas', [
            'empresa_id' => 'empresas',
        ], 'restrict');
    }

    public function down(): void
    {
        $this->rebindForeign('granjas', [
            'empresa_id' => 'empresas',
        ], 'cascade');

        $this->rebindForeign('galpones', [
            'empresa_id' => 'empresas',
            'granja_id' => 'granjas',
        ], 'cascade');

        $this->rebindForeign('lotes', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
        ], 'cascade');

        $this->rebindForeign('registros_operativos', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
            'user_id' => 'users',
        ], 'cascade');

        $this->rebindForeign('vacunaciones', [
            'empresa_id' => 'empresas',
            'galpon_id' => 'galpones',
            'lote_id' => 'lotes',
            'user_id' => 'users',
        ], 'cascade');
    }

    /**
     * @param  array<string, string>  $columns  column => parent table
     */
    private function rebindForeign(string $table, array $columns, string $onDelete): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            foreach (array_keys($columns) as $column) {
                $blueprint->dropForeign([$column]);
            }
        });

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $onDelete): void {
            foreach ($columns as $column => $parent) {
                $foreign = $blueprint->foreign($column)->references('id')->on($parent);

                if ($onDelete === 'restrict') {
                    $foreign->restrictOnDelete();
                } else {
                    $foreign->cascadeOnDelete();
                }
            }
        });
    }
};
