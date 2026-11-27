<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\LogOrderPlaced;
class OrderEventTest extends TestCase {
    public function test_order_event_queues_job(): void {
        Queue::fake();
        event('meal.order.placed',[123]);
        Queue::assertPushed(LogOrderPlaced::class,fn($job)=>$job->orderId===123);
    }
}
