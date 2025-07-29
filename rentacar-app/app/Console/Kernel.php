<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        // Registra aquí tu comando de pagos atrasados
        \App\Console\Commands\NotifyLatePayments::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecuta diariamente a las 08:00 el comando que notifica pagos atrasados
        $schedule
            ->command('notify:late-payments')
            ->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Carga automáticamente los comandos de app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Opcional: si tienes rutas de consola en routes/console.php
        require base_path('routes/console.php');
    }
}
