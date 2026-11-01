<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeColumn(true);
    }

    public function down(): void
    {
        if (DB::table('reports')->whereRaw('CHAR_LENGTH(key_info) > 1000')->exists()) {
            throw new RuntimeException('Cannot shrink report segments: saved content exceeds 1,000 characters.');
        }
        $this->changeColumn(false);
    }
    private function changeColumn(bool $expand): void
    {
        $connection = DB::connection();
        $mode = $connection->getDriverName() === 'mysql'
            ? $connection->selectOne('SELECT @@SESSION.sql_mode AS mode')->mode : null;
        try {
            if ($mode !== null) {
                // Table rebuilds revalidate legacy zero dates. Permit those existing values only
                // on this DDL connection; never rewrite dates or change the global SQL mode.
                $ddlMode = implode(',', array_diff(explode(',', $mode), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));
                $connection->statement('SET SESSION sql_mode = ?', [$ddlMode]);
            }
            Schema::table('reports', function (Blueprint $table) use ($expand) {
                if ($expand) $table->mediumText('key_info')->nullable()->change();
                else $table->string('key_info', 1000)->nullable()->change();
            });
        } finally {
            if ($mode !== null) $connection->statement('SET SESSION sql_mode = ?', [$mode]);
        }
    }
};
