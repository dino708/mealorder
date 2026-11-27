<?php
namespace App\Policies;
use App\Models\{Order,User};
class OrderPolicy {
    public function view(User $user, Order $order): bool {return $user->role==='admin' || $user->id===$order->user_id;}
}
