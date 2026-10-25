<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class WebsiteEnquiry extends Mailable
{
    public function __construct(public array $lead, public ?object $report = null, public ?string $clientIp = null, public ?int $enquiryId = null)
    {
    }

    public static function label(string $type): string
    {
        return ['1'=>'Sample Request', '2'=>'Discount Request', '3'=>'Enquiry Before Buying',
            '4'=>'Customization Request', '5'=>'Contact Enquiry', '6'=>'Research Enquiry', '7'=>'Table of Contents Request'][$type] ?? 'Website Enquiry';
    }

    public function build(): static
    {
        return $this->subject('New website enquiry - '.self::label($this->lead['type']))
            ->replyTo($this->lead['email_id'], $this->lead['full_name'])
            ->view('emails.website-enquiry')->text('emails.website-enquiry-text');
    }
}
