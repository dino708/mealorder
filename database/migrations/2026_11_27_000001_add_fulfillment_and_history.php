<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('orders',function(Blueprint $t){
   $t->string('status',30)->default('pending')->change();
   $t->string('fulfillment',12)->default('pickup');
   $t->text('delivery_address')->nullable();
   $t->string('contact_phone',20)->nullable();
   $t->decimal('delivery_fee',12,2)->unsigned()->default(0);
   $t->text('cancellation_reason')->nullable();
   $t->index(['status','created_at']);
  });
  Schema::table('preorder_schedules',fn(Blueprint $t)=>$t->dateTime('fulfillment_at')->nullable());
  Schema::create('order_status_histories',function(Blueprint $t){
   $t->id();$t->foreignId('order_id')->constrained()->restrictOnDelete();
   $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
   $t->string('status',30);$t->text('reason')->nullable();$t->timestamp('created_at')->useCurrent();
  });
 }
 public function down(): void {
  if (\Illuminate\Support\Facades\DB::table('orders')->where('status','on_delivery')->exists()) throw new \RuntimeException('Selesaikan pengiriman sebelum rollback.');
  Schema::dropIfExists('order_status_histories');
  Schema::table('preorder_schedules',fn(Blueprint $t)=>$t->dropColumn('fulfillment_at'));
  Schema::table('orders',function(Blueprint $t){
   $t->dropIndex(['status','created_at']);
   $t->dropColumn(['fulfillment','delivery_address','contact_phone','delivery_fee','cancellation_reason']);
   $t->enum('status',['pending','confirmed','processing','ready','completed','cancelled'])->default('pending')->change();
  });
 }
};
