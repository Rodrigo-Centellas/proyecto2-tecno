<?php
// App/Notifications/PagoAtrasadoNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PagoAtrasadoNotification extends Notification
{
    use Queueable;

    protected $pago;

    public function __construct($pago)
    {
        $this->pago = $pago;
    }

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Pago atrasado')
                    ->line("Tu pago #{$this->pago->id} de \${$this->pago->monto} está atrasado.")
                    ->action('Ver Mis Pagos', url('/mis-pagos'))
                    ->line('Por favor, regulariza tu pago lo antes posible.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'pago_id' => $this->pago->id,
            'mensaje' => "Tu pago #{$this->pago->id} está atrasado.",
            'tipo'    => 'pago_atrasado',
        ];
    }
}
