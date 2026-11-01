<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(DB::connection()->getDriverName()==='mysql'){
   $mode=DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
   try{DB::statement('SET SESSION sql_mode = ?',[implode(',',array_diff(explode(',',$mode),['NO_ZERO_DATE','NO_ZERO_IN_DATE']))]);DB::statement("ALTER TABLE checkouts MODIFY payment_by ENUM('PayPal','Wire Transfer','CCAvenue') NOT NULL");}finally{DB::statement('SET SESSION sql_mode = ?',[$mode]);}
  }
  if(!Schema::hasTable('lr_payment_attempts'))Schema::create('lr_payment_attempts',function(Blueprint $t){$t->uuid('id')->primary();$t->unsignedInteger('checkout_id')->index();$t->string('order_number',50)->index();$t->string('mode',10);$t->longText('credentials');$t->string('nonce',48);$t->string('status',30)->index();$t->decimal('amount',10,2);$t->string('currency',10);$t->string('tracking_id',50)->nullable()->index();$t->text('response')->nullable();$t->string('note',255)->nullable();$t->timestamp('verified_at')->nullable();$t->timestamps();});
 }
 public function down():void {throw new RuntimeException('Payment records must be retained. Disable CCAvenue in settings instead of dropping payment history.');}
};
