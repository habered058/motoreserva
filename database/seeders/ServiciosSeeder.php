<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Cambio de aceite',      'duracion_minutos' => 30, 'precio' => 45000],
            ['nombre' => 'Revisión general',       'duracion_minutos' => 60, 'precio' => 80000],
            ['nombre' => 'Cambio de llantas',      'duracion_minutos' => 45, 'precio' => 120000],
            ['nombre' => 'Ajuste de frenos',       'duracion_minutos' => 40, 'precio' => 60000],
            ['nombre' => 'Diagnóstico eléctrico',  'duracion_minutos' => 50, 'precio' => 70000],
        ];

        foreach ($servicios as $datos) {
            Servicio::firstOrCreate(['nombre' => $datos['nombre']], $datos);
        }
    }
}
