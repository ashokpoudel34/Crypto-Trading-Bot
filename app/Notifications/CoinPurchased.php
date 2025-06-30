<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoinPurchased extends Notification
{
    use Queueable;

    protected $purchases;

    public function __construct(array $purchases)
    {
        $this->purchases = $purchases;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Crypto Purchase Notification')
            ->line('Your trading bot has purchased the following coins:');

        foreach ($this->purchases as $coin) {
            $mail->line("• {$coin['name']} ({$coin['symbol']}): " .
                number_format($coin['amount'], 6) . " coins @ $" .
                number_format($coin['price'], 2) .
                " | 7d Change: {$coin['change']}%");
        }

        return $mail->action('View Portfolio', url('/dashboard'))
                    ->line('Thank you for using our trading bot!');
    }
}

