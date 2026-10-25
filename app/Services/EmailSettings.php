<?php

namespace App\Services;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class EmailSettings
{
    public function __construct(private ?string $path = null)
    {
        $this->path ??= app()->environmentFilePath();
    }

    private function read(): array
    {
        return is_file($this->path) ? Dotenv::parse(file_get_contents($this->path)) : [];
    }

    public function apply(): void
    {
        $env = $this->read();
        if (($env['MAIL_SETTINGS_MANAGED'] ?? '') !== 'true') return;
        $security = $env['MAIL_SECURITY'] ?? 'tls';
        config([
            'mail.default' => $env['MAIL_MAILER'] ?? 'smtp',
            'mail.mailers.hosting_relay' => [
                'transport' => 'smtp', 'scheme' => 'smtp', 'host' => '127.0.0.1', 'port' => 25,
                'username' => null, 'password' => null, 'auto_tls' => false, 'require_tls' => false,
                'timeout' => 10, 'local_domain' => parse_url(config('app.url'), PHP_URL_HOST),
            ],
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.scheme' => $security === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $env['MAIL_HOST'] ?? '',
            'mail.mailers.smtp.port' => (int) ($env['MAIL_PORT'] ?? 587),
            'mail.mailers.smtp.username' => $env['MAIL_USERNAME'] ?? '',
            'mail.mailers.smtp.password' => empty($env['MAIL_PASSWORD_ENCRYPTED']) ? null : Crypt::decryptString($env['MAIL_PASSWORD_ENCRYPTED']),
            'mail.mailers.smtp.auto_tls' => $security !== 'none',
            'mail.mailers.smtp.require_tls' => $security === 'tls',
            'mail.from.address' => $env['MAIL_FROM_ADDRESS'] ?? '',
            'mail.from.name' => $env['MAIL_FROM_NAME'] ?? '',
            'site.admin_email' => $env['MAIL_ADMIN_ADDRESS'] ?? '',
            'site.mail_notifications' => ($env['MAIL_NOTIFICATIONS'] ?? 'false') === 'true',
        ]);
    }

    public function values(): array
    {
        $env = $this->read();
        return [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'security' => $env['MAIL_SECURITY'] ?? (config('mail.mailers.smtp.scheme') === 'smtps' || (int) config('mail.mailers.smtp.port') === 465 ? 'ssl' : 'tls'),
            'username' => config('mail.mailers.smtp.username'),
            'has_password' => filled(config('mail.mailers.smtp.password')),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'admin_address' => config('site.admin_email'),
            'notifications' => (bool) config('site.mail_notifications'),
        ];
    }

    public function save(array $data): void
    {
        $lock = fopen(storage_path('framework/email-settings.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('Cannot lock email settings.');
        $temp = null;
        try {
            $env = $this->read();
            $password = $data['smtp_password'] ?? '';
            if ($password === '') {
                $password = !empty($env['MAIL_PASSWORD_ENCRYPTED']) ? Crypt::decryptString($env['MAIL_PASSWORD_ENCRYPTED']) : ($env['MAIL_PASSWORD'] ?? config('mail.mailers.smtp.password', ''));
            }
            if (!empty($data['clear_password'])) $password = '';
            $updates = [
                'MAIL_SETTINGS_MANAGED' => 'true', 'MAIL_MAILER' => $data['mailer'],
                'MAIL_HOST' => $data['host'] ?? '', 'MAIL_PORT' => (string) ($data['port'] ?? 587),
                'MAIL_SECURITY' => $data['security'], 'MAIL_SCHEME' => $data['security'] === 'ssl' ? 'smtps' : 'smtp',
                'MAIL_URL' => '', 'MAIL_USERNAME' => $data['username'] ?? '', 'MAIL_PASSWORD' => '',
                'MAIL_PASSWORD_ENCRYPTED' => $password === '' ? '' : Crypt::encryptString($password),
                'MAIL_FROM_ADDRESS' => $data['from_address'], 'MAIL_FROM_NAME' => $data['from_name'],
                'MAIL_ADMIN_ADDRESS' => $data['admin_address'],
                'MAIL_NOTIFICATIONS' => !empty($data['notifications']) ? 'true' : 'false',
            ];
            $content = file_get_contents($this->path);
            foreach ($updates as $key => $value) {
                $line = $key.'="'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '', ''], $value).'"';
                $pattern = '/^(?:export\s+)?'.preg_quote($key, '/').'\s*=.*$/m';
                $content = preg_match($pattern, $content) ? preg_replace_callback($pattern, fn () => $line, $content) : rtrim($content)."\n".$line."\n";
            }
            // Validate before replacing the only environment file.
            Dotenv::parse($content);
            $temp = tempnam(dirname($this->path), '.mail-');
            if (!$temp || file_put_contents($temp, $content) !== strlen($content)) throw new RuntimeException('Cannot write email settings.');
            chmod($temp, 0600);
            if (!rename($temp, $this->path)) throw new RuntimeException('Cannot replace environment file.');
            $temp = null;
            $this->apply();
        } finally {
            if ($temp && is_file($temp)) unlink($temp);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
