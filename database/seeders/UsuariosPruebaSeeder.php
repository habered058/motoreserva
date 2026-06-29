<?php

namespace Database\Seeders;

use App\Models\HorarioTecnico;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $rolTecnico = Role::where('name', 'tecnico')->first();
        $rolCliente = Role::where('name', 'cliente')->first();

        // Técnico de prueba
        $userTecnico = User::firstOrCreate(
            ['email' => 'tecnico@motoreserva.test'],
            [
                'name'               => 'Carlos Técnico',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
            ]
        );
        $userTecnico->assignRole($rolTecnico);

        $tecnico = Tecnico::firstOrCreate(
            ['user_id' => $userTecnico->id],
            ['especialidad' => 'Mecánica general']
        );

        // Horario: lunes a viernes 08:00-17:00
        foreach ([1, 2, 3, 4, 5] as $dia) {
            HorarioTecnico::firstOrCreate([
                'tecnico_id' => $tecnico->id,
                'dia_semana' => $dia,
            ], [
                'hora_inicio' => '08:00',
                'hora_fin'    => '17:00',
            ]);
        }

        // Cliente de prueba
        $userCliente = User::firstOrCreate(
            ['email' => 'cliente@motoreserva.test'],
            [
                'name'              => 'Ana Cliente',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $userCliente->assignRole($rolCliente);
    }
}
