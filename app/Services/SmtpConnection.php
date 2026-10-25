<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class SmtpConnection
{
    public function verify(array $configuration): void
    {
        $transport = Mail::build($configuration)->getSymfonyTransport();
        try {
            $transport->start();
        } finally {
            $transport->stop();
        }
    }
}
