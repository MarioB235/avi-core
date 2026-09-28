<?php

namespace Tests\Unit\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use App\Support\CapturaCeroEstado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CapturaCeroEstadoTest extends TestCase
{
    public function test_resolver_estado_dia_returns_omision_when_empty(): void
    {
        $estado = CapturaCeroEstado::resolverEstadoDia(
            new Collection,
            RegistroOperativoTipo::Huevos,
        );

        $this->assertSame(CapturaCeroEstado::OMISION, $estado);
    }

    public function test_resolver_estado_dia_returns_registrado_when_sum_is_positive(): void
    {
        $registros = new Collection([
            $this->registroHuevos(huevos: 120),
        ]);

        $estado = CapturaCeroEstado::resolverEstadoDia($registros, RegistroOperativoTipo::Huevos);

        $this->assertSame(CapturaCeroEstado::REGISTRADO, $estado);
    }

    public function test_resolver_estado_dia_returns_cero_confirmado_when_flag_set_without_quantities(): void
    {
        $registros = new Collection([
            $this->registroHuevos(ceroConfirmado: true),
        ]);

        $estado = CapturaCeroEstado::resolverEstadoDia($registros, RegistroOperativoTipo::Huevos);

        $this->assertSame(CapturaCeroEstado::CERO_CONFIRMADO, $estado);
    }

    public function test_resolver_estado_dia_returns_omision_when_zero_without_confirmation(): void
    {
        $registros = new Collection([
            $this->registroMuertes(muertes: 0),
        ]);

        $estado = CapturaCeroEstado::resolverEstadoDia($registros, RegistroOperativoTipo::Muertes);

        $this->assertSame(CapturaCeroEstado::OMISION, $estado);
    }

    public function test_assert_tipo_permite_cero_confirmado_rejects_alimento(): void
    {
        $this->expectException(ValidationException::class);

        CapturaCeroEstado::assertTipoPermiteCeroConfirmado(RegistroOperativoTipo::Alimento);
    }

    public function test_etiqueta_estado_maps_known_states(): void
    {
        $this->assertSame('0 confirmado', CapturaCeroEstado::etiquetaEstado(CapturaCeroEstado::CERO_CONFIRMADO));
        $this->assertSame('Con registro', CapturaCeroEstado::etiquetaEstado(CapturaCeroEstado::REGISTRADO));
        $this->assertSame('Sin registro', CapturaCeroEstado::etiquetaEstado(CapturaCeroEstado::OMISION));
    }

    private function registroHuevos(int $huevos = 0, bool $ceroConfirmado = false): RegistroOperativo
    {
        $registro = new RegistroOperativo([
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => $huevos,
            'huevos_descarte' => 0,
            'cero_confirmado' => $ceroConfirmado,
        ]);

        $registro->syncOriginal();

        return $registro;
    }

    private function registroMuertes(int $muertes = 0, bool $ceroConfirmado = false): RegistroOperativo
    {
        $registro = new RegistroOperativo([
            'tipo' => RegistroOperativoTipo::Muertes,
            'muertes' => $muertes,
            'cero_confirmado' => $ceroConfirmado,
        ]);

        $registro->syncOriginal();

        return $registro;
    }
}
