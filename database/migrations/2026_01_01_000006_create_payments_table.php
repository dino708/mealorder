<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
$t->id(); $t->foreignId('order_id')->unique()->constrained()->restrictOnDelete(); $t->string('method',50); $t->decimal('amount',12,2)->unsigned(); $t->enum('status',['unpaid','waiting_confirmation','paid','rejected'])->default('unpaid'); $t->dateTime('paid_at')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
