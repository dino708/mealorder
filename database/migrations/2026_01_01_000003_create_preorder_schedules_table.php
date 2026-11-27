<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('preorder_schedules', function (Blueprint $t) {
$t->id(); $t->string('title',150); $t->dateTime('start_at'); $t->dateTime('end_at'); $t->unsignedInteger('quota'); $t->enum('status',['draft','open','closed'])->default('draft'); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('preorder_schedules'); }
};
