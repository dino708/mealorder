<?php
namespace App\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
class LogOrderPlaced implements ShouldQueue {
    use Queueable;
    public int $tries=3;
    public function __construct(public int $orderId) {}
    public function handle(): void {
        Log::info('Meal Order: pesanan baru diterima',['order_id'=>$this->orderId]);
    }
}
