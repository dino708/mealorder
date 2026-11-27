<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderPlaced;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {
        Event::listen('meal.order.placed',function(int $orderId){OrderPlaced::dispatch($orderId);});
    }
}
