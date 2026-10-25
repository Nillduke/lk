<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'reports' => [
            'lr_reports_slug_status_idx' => ['slug', 'status'],
            'lr_reports_live_date_idx' => ['status', 'pub_date'],
            'lr_reports_live_category_date_idx' => ['status', 'category_id', 'pub_date'],
        ],
        'reports_licenses' => ['lr_report_license_min_idx' => ['report_id', 'lic_price']],
        'news' => [
            'lr_news_live_date_idx' => ['status', 'deleted_at', 'created_at'],
            'lr_news_slug_live_idx' => ['slug', 'status', 'deleted_at'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (!Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }
};
