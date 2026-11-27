<?php
namespace App\Listeners;
use App\Events\OrderPlaced;
use App\Jobs\LogOrderPlaced;
class QueueOrderNotification {
    public function handle(OrderPlaced $event): void {
        LogOrderPlaced::dispatch($event->orderId)->afterCommit();
    }
}
