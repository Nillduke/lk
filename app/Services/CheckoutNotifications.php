<?php
namespace App\Services;

use App\Mail\CheckoutReceived;
use App\Support\AdminMailRecipients;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutNotifications
{
    public function created(object $order, object $report): void
    {
        if (!config('site.mail_notifications')) return;
        foreach (['admin' => config('site.admin_email'), 'customer' => $order->email] as $audience => $recipient) {
            if (!$recipient) continue;
            try {
                $mail = Mail::to($recipient);
                if ($audience === 'admin' && ($bcc = AdminMailRecipients::bcc())) $mail->bcc($bcc);
                $sent = $mail->send(new CheckoutReceived($order, $report, $audience === 'admin'));
                Log::info('Checkout email accepted by transport', ['checkout_id' => $order->id, 'order_number' => $order->order_number,
                    'audience' => $audience, 'mailer' => config('mail.default'), 'message_id' => $sent?->getMessageId()]);
            } catch (\Throwable $e) {
                Log::error('Checkout email failed', ['checkout_id' => $order->id, 'order_number' => $order->order_number,
                    'audience' => $audience, 'exception' => get_class($e)]);
                report($e);
            }
        }
    }
}
