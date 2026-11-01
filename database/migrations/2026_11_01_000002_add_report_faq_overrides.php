<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {if(!Schema::hasTable('lr_report_faqs'))Schema::create('lr_report_faqs',function(Blueprint $t){$t->unsignedBigInteger('report_id')->primary();$t->string('mode',10)->default('custom');$t->longText('faqs');$t->timestamps();});}
    public function down(): void {Schema::dropIfExists('lr_report_faqs');}
};
