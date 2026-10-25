<?php

namespace App\Support;

class AdminMailRecipients
{
    public static function parse(?string $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($email) => strtolower(trim($email)), explode(',', $value ?? '')
        ))));
    }

    public static function bcc(): array
    {
        return array_values(array_diff(self::parse(config('site.admin_bcc')), [strtolower((string) config('site.admin_email'))]));
    }
}
