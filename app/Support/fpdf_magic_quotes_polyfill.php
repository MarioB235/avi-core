<?php

/**
 * FPDF 1.8 aún referencia magic quotes (eliminadas en PHP 8.0).
 * avicore-defer: polyfill hasta migrar generador PDF; disparador: reemplazo de setasign/fpdf o wrapper sin magic quotes.
 */
if (! function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime(): bool
    {
        return false;
    }
}

if (! function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting): bool
    {
        return false;
    }
}
