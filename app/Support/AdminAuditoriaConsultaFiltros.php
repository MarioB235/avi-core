<?php

namespace App\Support;

readonly class AdminAuditoriaConsultaFiltros
{
    public function __construct(
        public ?string $categoria = null,
        public ?int $actorId = null,
        public ?string $accion = null,
        public ?string $fechaDesde = null,
        public ?string $fechaHasta = null,
    ) {}
}
