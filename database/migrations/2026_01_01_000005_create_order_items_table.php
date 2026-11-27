<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('order_items', function (Blueprint $t) {
$t->id(); $t->foreignId('order_id')->constrained()->restrictOnDelete(); $t->foreignId('product_id')->constrained()->restrictOnDelete(); $t->string('product_name',150); $t->decimal('price',12,2)->unsigned(); $t->unsignedInteger('quantity'); $t->decimal('subtotal',12,2)->unsigned();
        });
    }
    public function down(): void { Schema::dropIfExists('order_items'); }
};
