<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoEnquiryLinks implements ValidationRule
{
    public function __construct(private bool $email = false) {}

    public function containsLink(string $value): bool
    {
        for ($i=0;$i<2;$i++) $value = rawurldecode(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (!mb_check_encoding($value, 'UTF-8')) return true;
        if (class_exists(\Normalizer::class)) $value = \Normalizer::normalize($value, \Normalizer::FORM_KC) ?: $value;
        $value = preg_replace_callback('/[\x{FF01}-\x{FF5E}]/u', fn($m)=>chr(mb_ord($m[0],'UTF-8')-0xFEE0), $value);
        $value = str_replace(["\u{3002}","\u{FF61}"], '.', $value);
        $value = preg_replace('/\p{Cf}/u', '', $value);
        $value = preg_replace('/\[(?:\.|dot)\]|\((?:\.|dot)\)/i', '.', $value);
        if (preg_match('~\b(?:https?|hxxps?|ftp|mailto|tel|javascript)\s*:|\bdata\s*:\s*[\w.+-]+/[\w.+-]+[;,]|www\s*\.|<\s*a\b|\b(?:href|src)\s*=|\[/?url(?:[=\]])|\[[^\]\r\n]*\]\([^\r\n)]*\)~iu', $value)) return true;
        // A real email must have a domain. Do not mistake that domain for a website link.
        if ($this->email) return false;
        if (preg_match('~(?<![\pL\pN_])(?:[\pL\pN](?:[\pL\pN-]{0,62})\.)+(?:[\pL]{2,63}|xn--[a-z0-9-]{2,59})(?![\pL\pN_-])~iu', $value)) return true;
        preg_match_all('~(?<![\pL\pN_.])(?:\d{1,3}\.){3}\d{1,3}(?![\pL\pN_.])~u', $value, $ips);
        foreach ($ips[0] as $ip) if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return true;
        return (bool)preg_match('~(?:^|\s)//[a-z0-9]~iu', $value);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) return;
        if ($this->containsLink((string)$value)) $fail('Links and website addresses are not allowed in this field.');
    }
}
