<?php

namespace App\Enums;

enum RetencionD07Categoria: string
{
    case Operativa = 'operativa';
    case Auditoria = 'auditoria';
    case Correcciones = 'correcciones';
    case DocumentosEmitidos = 'documentos_emitidos';
    case Usuarios = 'usuarios';
}
