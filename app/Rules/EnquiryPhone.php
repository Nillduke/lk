<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnquiryPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) return;
        $value = trim((string)$value);
        $main = preg_replace('/\s*(?:ext\.?|x|#)\s*\d{1,8}$/i', '', $value);
        $digits = preg_replace('/\D/', '', $main);
        if (preg_match('/[\r\n]/',$value) || !preg_match('/^\+?[0-9 ()\-.]+(?:\s*(?:ext\.?|x|#)\s*\d{1,8})?$/iD', $value) || strlen($digits)<7 || strlen($digits)>15) {
            $fail('Enter a valid phone number with 7 to 15 digits; an optional extension is allowed.');
        }
    }
}
