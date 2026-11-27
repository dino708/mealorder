<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $t) {
$t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete(); $t->foreignId('schedule_id')->constrained('preorder_schedules')->restrictOnDelete(); $t->string('order_code',30)->unique(); $t->decimal('total_amount',12,2)->unsigned(); $t->enum('status',['pending','confirmed','processing','ready','completed','cancelled'])->default('pending'); $t->text('notes')->nullable(); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
