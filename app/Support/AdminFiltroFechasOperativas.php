<?php

namespace App\Support;

final class AdminFiltroFechasOperativas
{
    public static function fechaMaxima(?int $empresaId): string
    {
        return $empresaId !== null
            ? DiaOperativoEmpresa::fechaLogicaHoy($empresaId)
            : now()->toDateString();
    }

    /**
     * @return array<string, list<string>>
     */
    public static function reglas(?int $empresaId): array
    {
        $fechaMaxima = self::fechaMaxima($empresaId);

        return [
            'fechaDesde' => ['nullable', 'date', 'before_or_equal:'.$fechaMaxima],
            'fechaHasta' => ['nullable', 'date', 'before_or_equal:'.$fechaMaxima, 'after_or_equal:fechaDesde'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensajes(): array
    {
        return [
            'fechaDesde.date' => 'La fecha desde no es válida.',
            'fechaDesde.before_or_equal' => 'La fecha desde no puede ser futura.',
            'fechaHasta.date' => 'La fecha hasta no es válida.',
            'fechaHasta.before_or_equal' => 'La fecha hasta no puede ser futura.',
            'fechaHasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validar(?string $fechaDesde, ?string $fechaHasta, ?int $empresaId): array
    {
        $validator = validator(
            [
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
            ],
            self::reglas($empresaId),
            self::mensajes(),
        );

        if ($validator->fails()) {
            $errores = [];

            foreach ($validator->errors()->messages() as $field => $messages) {
                $errores[$field] = $messages[0];
            }

            return $errores;
        }

        return [];
    }
}
