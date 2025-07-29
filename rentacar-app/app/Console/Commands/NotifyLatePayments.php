<?php
// app/Console/Commands/NotifyLatePayments.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Notifications\PagoAtrasadoNotification;

class NotifyLatePayments extends Command
{
    protected $signature = 'notify:late-payments';
    protected $description = 'Enviar notificaciones de pagos atrasados a los clientes';

    public function handle()
    {
        $pagosAtrasados = Pago::with('reserva.user')
            ->where('estado', 'pendiente')
            ->whereDate('hasta', '<', now())
            ->get();

        foreach ($pagosAtrasados as $pago) {
            $usuario = $pago->reserva->user;
            $usuario->notify(new PagoAtrasadoNotification($pago));
        }

        $this->info('Pagos atrasados notificados: ' . $pagosAtrasados->count());
    }
}
