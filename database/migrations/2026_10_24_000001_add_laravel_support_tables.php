<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lr_password_reset_tokens')) {
            Schema::create('lr_password_reset_tokens', function (Blueprint $t) {
                $t->engine = 'InnoDB';
                $t->string('email')->primary();
                $t->string('token');
                $t->timestamp('created_at')->nullable();
            });
        }
        if (! Schema::hasTable('lr_pages')) {
            Schema::create('lr_pages', function (Blueprint $t) {
                $t->engine = 'InnoDB';
                $t->id();
                $t->string('slug')->unique();
                $t->string('title');
                $t->longText('content');
                $t->boolean('status')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('lr_bug_reports')) {
            Schema::create('lr_bug_reports', function (Blueprint $t) {
                $t->engine = 'InnoDB';
                $t->id();
                $t->unsignedInteger('user_id');
                $t->string('title');
                $t->text('description');
                $t->string('status')->default('open');
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lr_pages');
        Schema::dropIfExists('lr_password_reset_tokens');
        Schema::dropIfExists('lr_bug_reports');
    }
};
