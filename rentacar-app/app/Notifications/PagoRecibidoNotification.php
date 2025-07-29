<?php
// App/Notifications/PagoRecibidoNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PagoRecibidoNotification extends Notification
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
                    ->subject('Se recibió un nuevo pago')
                    ->line("Se ha marcado como pagado el pago #{$this->pago->id}.")
                    ->action('Ver Pagos', url('/pagos'))
                    ->line('Gracias por usar nuestra aplicación.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'pago_id' => $this->pago->id,
            'mensaje' => "Pago #{$this->pago->id} recibido por \${$this->pago->monto}.",
            'tipo'    => 'pago_recibido',
        ];
    }
}
