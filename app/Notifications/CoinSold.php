<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoinSold extends Notification
{
    use Queueable;

    protected $coin;
    protected $amount;
    protected $price;

    public function __construct(array $solddata)
    {
        $this->solddata = $solddata;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Crypto Sold Notification')
            ->line('Your trading bot has Sold the following coins:');

        foreach ($this->solddata as $coin) {
            $mail->line("• {$coin['name']} ({$coin['symbol']}): " .
                number_format($coin['amount'], 6) . " coins @ $" .
                number_format($coin['price'], 2) .
                " | 7d Change: {$coin['change']}%");
        }

        return $mail->action('View Portfolio', url('/dashboard'))
                    ->line('Thank you for using our trading bot!');
    }
}
