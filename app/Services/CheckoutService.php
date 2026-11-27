<?php
namespace App\Services;
use App\Models\{Order,Product,PreorderSchedule,OrderItem};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CheckoutService {
    private function fail(string $message): never { throw ValidationException::withMessages(['order'=>$message]); }
    private function cents(string $price): int {
        [$whole,$fraction]=array_pad(explode('.',$price,2),2,'');
        return (int)$whole*100+(int)str_pad($fraction,2,'0');
    }
    private function money(int $cents): string { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
    public function checkout(int $userId,array $data): Order {
        return DB::transaction(function () use ($userId,$data) {
            $schedule=PreorderSchedule::lockForUpdate()->findOrFail($data['schedule_id']);
            if ($schedule->status!=='open' || now()->lt($schedule->start_at) || now()->gte($schedule->end_at)) $this->fail('Periode PO tidak aktif.');
            $qty=collect($data['items'])->groupBy('product_id')->map(fn($items)=>$items->sum('quantity'))->sortKeys();
            $used=OrderItem::whereHas('order',fn($q)=>$q->where('schedule_id',$schedule->id)->where('status','!=','cancelled'))->sum('quantity');
            if ($schedule->fulfillment_at && now()->gte($schedule->fulfillment_at)) $this->fail('Waktu pengambilan sudah lewat.');
            if ($used+$qty->sum()>$schedule->quota) $this->fail('Kuota PO tidak mencukupi.');
            $products=Product::whereIn('id',$qty->keys())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $items=[]; $total=0;
            foreach ($qty as $id=>$n) {
                $p=$products->get($id);
                if (!$p || !$p->is_available || $p->stock<$n) $this->fail('Stok tidak mencukupi.');
                $subtotal=$this->cents($p->price)*$n; $total+=$subtotal;
                if ($total>999999999999) $this->fail('Total terlalu besar.');
                $items[]=['product_id'=>$id,'product_name'=>$p->name,'product_image'=>$p->image,'price'=>$p->price,'quantity'=>$n,'subtotal'=>$this->money($subtotal)];
                $p->decrement('stock',$n);
            }
            $fulfillment=$data['fulfillment']??'pickup';
            if ($fulfillment==='delivery' && !config('meal.delivery_enabled')) $this->fail('Pengiriman tidak tersedia. Pilih pengambilan langsung.');
            $fee=$fulfillment==='delivery'?max(0,(int)config('meal.delivery_fee'))*100:0;
            $total+=$fee;
            if ($total>999999999999) $this->fail('Total terlalu besar.');
            $order=Order::create(['fulfillment'=>$fulfillment,'delivery_fee'=>$this->money($fee),
                'delivery_address'=>$fulfillment==='delivery'?($data['delivery_address']??null):null,
                'contact_phone'=>$data['contact_phone']??null,'user_id'=>$userId,'schedule_id'=>$schedule->id,'order_code'=>'MO-'.Str::ulid(),
                'total_amount'=>$this->money($total),'status'=>'pending','notes'=>$data['notes']??null]);
            $order->items()->createMany($items);
            $order->payment()->create(['method'=>$data['payment_method']??'manual','amount'=>$order->total_amount,'status'=>'unpaid']);
            $order->history()->create(['actor_id'=>$userId,'status'=>'pending','created_at'=>now()]);
            return $order->load('items','payment','history','schedule');
        },3);
    }
    public function transition(Order $order,string $next,?string $reason=null,?int $actor=null): Order {
        return DB::transaction(function () use ($order,$next,$reason,$actor) {
            PreorderSchedule::lockForUpdate()->findOrFail($order->schedule_id);
            $order=Order::lockForUpdate()->findOrFail($order->id);
            if ($order->status===$next) return $order->load('items','payment','history','schedule');
            $allowed=['pending'=>['confirmed','cancelled'],'confirmed'=>['processing','cancelled'],
                'processing'=>['ready'],'ready'=>['completed']];
            if (!in_array($next,$allowed[$order->status]??[],true)) $this->fail('Transisi status tidak valid.');
            if ($next==='cancelled') {
                if (!trim($reason??'')) $this->fail('Masukkan alasan pembatalan.');
                if ($order->payment?->status==='paid') $this->fail('Catat pengembalian pembayaran secara manual terlebih dahulu.');

                foreach ($order->items()->orderBy('product_id')->get() as $item) {
                    Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id)->increment('stock',$item->quantity);
                }
            }
            if ($next==='completed' && $order->payment?->status!=='paid') $this->fail('Konfirmasi pembayaran terlebih dahulu.');
            $order->update(['status'=>$next,'cancellation_reason'=>$next==='cancelled'?$reason:null]);
            $order->history()->create(['actor_id'=>$actor,'status'=>$next,'reason'=>$reason,'created_at'=>now()]);
            return $order->load('items','payment','history','schedule');
        },3);
    }
    public function payment(Order $order,string $status): Order {
        return DB::transaction(function () use ($order,$status) {
            $order=Order::lockForUpdate()->findOrFail($order->id);
            if (in_array($order->status,['cancelled','completed'],true)) $this->fail('Pesanan sudah ditutup.');
            $order->payment()->update(['status'=>$status,'paid_at'=>$status==='paid'?now():null]);
            return $order->load('items','payment','history','schedule');
        });
    }
}
