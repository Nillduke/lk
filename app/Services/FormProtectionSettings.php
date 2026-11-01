<?php
namespace App\Services;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class FormProtectionSettings
{
    public function __construct(private ?string $path = null) { $this->path ??= app()->environmentFilePath(); }
    private function read(): array { return is_file($this->path) ? Dotenv::parse(file_get_contents($this->path)) : []; }
    private function secret(array $env): string
    {
        if (!empty($env['TURNSTILE_SECRET_ENCRYPTED'])) {
            try { return Crypt::decryptString($env['TURNSTILE_SECRET_ENCRYPTED']); } catch (\Throwable) { return ''; }
        }
        return (string)($env['TURNSTILE_SECRET_KEY'] ?? config('site.turnstile.secret',''));
    }
    public function apply(): void
    {
        $env = $this->read();
        if (!array_key_exists('TURNSTILE_ENABLED', $env) && !array_key_exists('TURNSTILE_SECRET_KEY',$env) && !array_key_exists('TURNSTILE_SECRET_ENCRYPTED',$env)) return;
        config(['site.turnstile.enabled'=>filter_var($env['TURNSTILE_ENABLED'] ?? false,FILTER_VALIDATE_BOOLEAN),
            'site.turnstile.site_key'=>$env['TURNSTILE_SITE_KEY'] ?? '', 'site.turnstile.secret'=>$this->secret($env)]);
    }
    public function values(): array
    {
        $env = $this->read();
        return ['enabled'=>(bool)config('site.turnstile.enabled'), 'site_key'=>config('site.turnstile.site_key') ?: '0x4AAAAAAFhJQb7ICoiNOmBA', 'has_secret'=>$this->secret($env) !== ''];
    }
    public function save(array $data): void
    {
        // Share the same lock as SMTP settings, since both atomically update the one .env file.
        $lock = fopen(storage_path('framework/email-settings.lock'),'c');
        if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('Cannot lock settings.');
        $temp = null;
        try {
            $env = $this->read();
            $secret = trim($data['turnstile_secret'] ?? '') ?: $this->secret($env);
            if (!empty($data['enabled']) && $secret === '') throw new RuntimeException('A Turnstile secret is required.');
            $updates = ['TURNSTILE_ENABLED'=>!empty($data['enabled'])?'true':'false','TURNSTILE_SITE_KEY'=>$data['site_key'],
                'TURNSTILE_SECRET_KEY'=>'','TURNSTILE_SECRET_ENCRYPTED'=>$secret === '' ? '' : Crypt::encryptString($secret)];
            $content = is_file($this->path) ? file_get_contents($this->path) : '';
            foreach ($updates as $key=>$value) {
                $line=$key.'="'.str_replace(['\\','"','$',"\r","\n"],['\\\\','\\"','\\$','',''],$value).'"';
                $pattern='/^(?:export\s+)?'.preg_quote($key,'/').'\s*=.*$/m';
                $content=preg_match($pattern,$content)?preg_replace_callback($pattern,fn()=>$line,$content):rtrim($content)."\n".$line."\n";
            }
            Dotenv::parse($content);
            $temp=tempnam(dirname($this->path),'.forms-');
            if (!$temp || file_put_contents($temp,$content)!==strlen($content)) throw new RuntimeException('Cannot save settings.');
            chmod($temp,0600);
            if (!rename($temp,$this->path)) throw new RuntimeException('Cannot replace environment settings.');
            $temp=null;$this->apply();
        } finally { if($temp&&is_file($temp))unlink($temp);flock($lock,LOCK_UN);fclose($lock); }
    }
}
