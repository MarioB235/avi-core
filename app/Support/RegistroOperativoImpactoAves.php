<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;

class RegistroOperativoImpactoAves
{
    /**
     * Aves a devolver al galpón al anular un registro activo (D07 / AUD-09).
     * Incluye tipo legado `combinado` con la misma lógica que muertes + descarte.
     */
    public static function cantidadARestaurarEnAnulacion(RegistroOperativo $registro): int
    {
        if ($registro->cero_confirmado) {
            return 0;
        }

        return match ($registro->tipo) {
            RegistroOperativoTipo::Muertes => RegistroOperativoCorreccion::cantidadMuertesEfectiva($registro),
            RegistroOperativoTipo::Descarte => RegistroOperativoCorreccion::cantidadDescarteEfectiva($registro),
            RegistroOperativoTipo::Combinado => RegistroOperativoCorreccion::cantidadMuertesEfectiva($registro)
                + RegistroOperativoCorreccion::cantidadDescarteEfectiva($registro),
            default => 0,
        };
    }

    public static function afectaAvesVivasEnAnulacion(RegistroOperativo $registro): bool
    {
        return self::cantidadARestaurarEnAnulacion($registro) > 0;
    }
}
