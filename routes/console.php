<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('app:check-connections', function () {
    foreach ([config('database.default'), 'reportdb'] as $name) {
        try {
            $db = DB::connection($name);
            $db->select('SELECT 1');
            $this->info($name.': connected to '.$db->getDatabaseName());
        } catch (Throwable $e) {
            $this->error($name.': connection failed');

            return 1;
        }
    }
    $this->line('Mail transport: '.config('mail.default'));
    $this->line('All connection settings: '.base_path('.env'));

    return 0;
})->purpose('Check both databases and display the configured mail transport without exposing credentials');
