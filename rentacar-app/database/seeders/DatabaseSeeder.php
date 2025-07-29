<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehiculo;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

         $this->call(UserSeeder::class);
        $this->call(VehicleSeeder::class);
         $this->call(NroCuentaSeeder::class);
         $this->call(FrecuenciaPagoSeeder::class);
         $this->call(ClausulaSeedeer::class);
         //$this->call(PagoSeeder::class);
    }
}
    