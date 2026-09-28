<?php

namespace App\Enums;

enum DocumentoEmitidoFormato: string
{
    case Pdf = 'pdf';
    case Xlsx = 'xlsx';

    public function extension(): string
    {
        return match ($this) {
            self::Pdf => 'pdf',
            self::Xlsx => 'xlsx',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Pdf => 'application/pdf',
            self::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }
}
