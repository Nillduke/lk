<?php

return [
    'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    'admin_email' => env('MAIL_ADMIN_ADDRESS'), 'mail_notifications' => env('MAIL_NOTIFICATIONS', true),
    'sales_email' => env('SITE_SALES_EMAIL'), 'phone' => env('SITE_PHONE'),
    'recaptcha' => ['enabled' => env('RECAPTCHA_ENABLED', false), 'site_key' => env('RECAPTCHA_SITE_KEY'), 'secret' => env('RECAPTCHA_SECRET_KEY')],
    'google' => ['client_id' => env('GOOGLE_CLIENT_ID'), 'client_secret' => env('GOOGLE_CLIENT_SECRET'), 'redirect' => env('GOOGLE_REDIRECT_URI')],
    'paypal' => ['enabled' => env('PAYPAL_ENABLED', false), 'mode' => env('PAYPAL_MODE', 'sandbox'), 'client_id' => env('PAYPAL_CLIENT_ID'), 'secret' => env('PAYPAL_CLIENT_SECRET'), 'business' => env('PAYPAL_BUSINESS_EMAIL'), 'currency' => env('PAYPAL_CURRENCY', 'USD')],
];
