<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class CheckoutReceived extends Mailable
{
    public function __construct(public object $order, public object $report, public bool $forAdmin = false) {}

    public function build(): self
    {
        $this->subject(($this->forAdmin ? 'New checkout - ' : 'Order received - ').$this->order->order_number)
            ->view('emails.checkout-received')->text('emails.checkout-received-text');
        if ($this->forAdmin) $this->replyTo($this->order->email, $this->order->name);
        return $this;
    }
}
