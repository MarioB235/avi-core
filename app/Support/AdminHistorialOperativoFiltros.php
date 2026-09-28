<?php

namespace App\Support;

readonly class AdminHistorialOperativoFiltros
{
    public function __construct(
        public ?int $granjaId = null,
        public ?int $galponId = null,
        public ?int $userId = null,
        public ?string $tipo = null,
        public ?string $estado = null,
        public ?string $fechaDesde = null,
        public ?string $fechaHasta = null,
    ) {}
}
