<?php

namespace App\Enums;

enum LoteEstado: string
{
    case Activo = 'activo';
    case EnProduccion = 'en_produccion';
    case Trasladado = 'trasladado';
    case Cerrado = 'cerrado';
}
