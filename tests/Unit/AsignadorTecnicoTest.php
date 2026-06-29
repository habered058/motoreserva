<?php

namespace Tests\Unit;

use App\Models\HorarioTecnico;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Tecnico;
use App\Models\User;
use App\Services\AsignadorTecnico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignadorTecnicoTest extends TestCase
{
    use RefreshDatabase;

    private Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = Servicio::create([
            'nombre'           => 'Cambio de aceite',
            'duracion_minutos' => 30,
            'precio'           => 45000,
        ]);
    }

    public function test_asigna_tecnico_disponible(): void
    {
        $lunes   = $this->proximaFechaDeDia(1);
        $tecnico = $this->crearTecnicoConHorario(1, '08:00', '17:00');

        $resultado = AsignadorTecnico::encontrarDisponible(
            $this->servicio->id, $lunes, '09:00'
        );

        $this->assertNotNull($resultado);
        $this->assertEquals($tecnico->id, $resultado->id);
    }

    public function test_descarta_tecnico_ocupado_y_asigna_al_libre(): void
    {
        $lunes          = $this->proximaFechaDeDia(1);
        $tecnicoOcupado = $this->crearTecnicoConHorario(1, '08:00', '17:00');
        $tecnicoLibre   = $this->crearTecnicoConHorario(1, '08:00', '17:00');

        $this->crearReserva($tecnicoOcupado->id, $lunes, '09:00:00');

        $resultado = AsignadorTecnico::encontrarDisponible(
            $this->servicio->id, $lunes, '09:00'
        );

        $this->assertNotNull($resultado);
        $this->assertEquals($tecnicoLibre->id, $resultado->id);
    }

    public function test_devuelve_null_cuando_ninguno_disponible(): void
    {
        $lunes   = $this->proximaFechaDeDia(1);
        $tecnico = $this->crearTecnicoConHorario(1, '08:00', '17:00');

        $this->crearReserva($tecnico->id, $lunes, '09:00:00');

        $resultado = AsignadorTecnico::encontrarDisponible(
            $this->servicio->id, $lunes, '09:00'
        );

        $this->assertNull($resultado);
    }

    public function test_no_descarta_tecnico_con_reserva_cancelada(): void
    {
        $lunes   = $this->proximaFechaDeDia(1);
        $tecnico = $this->crearTecnicoConHorario(1, '08:00', '17:00');

        $this->crearReserva($tecnico->id, $lunes, '09:00:00', Reserva::ESTADO_CANCELADA);

        $resultado = AsignadorTecnico::encontrarDisponible(
            $this->servicio->id, $lunes, '09:00'
        );

        $this->assertNotNull($resultado);
        $this->assertEquals($tecnico->id, $resultado->id);
    }

    private function crearTecnicoConHorario(int $diaSemana, string $inicio, string $fin): Tecnico
    {
        $user    = User::factory()->create();
        $tecnico = Tecnico::create(['user_id' => $user->id, 'especialidad' => 'General']);

        HorarioTecnico::create([
            'tecnico_id'  => $tecnico->id,
            'dia_semana'  => $diaSemana,
            'hora_inicio' => $inicio,
            'hora_fin'    => $fin,
        ]);

        return $tecnico;
    }

    private function crearReserva(
        int $tecnicoId,
        string $fecha,
        string $hora,
        string $estado = Reserva::ESTADO_PENDIENTE
    ): void {
        Reserva::create([
            'cliente_id'  => User::factory()->create()->id,
            'tecnico_id'  => $tecnicoId,
            'servicio_id' => $this->servicio->id,
            'fecha'       => $fecha,
            'hora'        => $hora,
            'marca_moto'  => 'Honda',
            'modelo_moto' => 'CB125',
            'placa'       => 'ABC123',
            'estado'      => $estado,
        ]);
    }

    private function proximaFechaDeDia(int $diaSemana): string
    {
        $hoy  = now();
        $diff = ($diaSemana - $hoy->dayOfWeek + 7) % 7;
        $diff = $diff === 0 ? 7 : $diff;

        return $hoy->addDays($diff)->format('Y-m-d');
    }
}
