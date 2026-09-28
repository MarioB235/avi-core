<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class AuditoriaCriticaException extends RuntimeException
{
    public function __construct(string $message = 'No se pudo registrar la auditoría crítica.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
