<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            'name'       => 'Admin',
            'email'      => 'admin@smartroom.local',
            'password'   => Hash::make('password'),
            'is_admin'   => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('actuator_states')->insertOrIgnore([
            ['actuator_name' => 'led',    'state' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['actuator_name' => 'buzzer', 'state' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}