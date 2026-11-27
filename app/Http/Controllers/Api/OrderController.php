<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\{Order,User};
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class OrderController extends Controller {
 public function index(Request $r){return Order::where('user_id',$r->user()->id)->with('items','payment','history','schedule')->latest()->get();}
 public function show(Order $order){Gate::authorize('view',$order);return $order->load('items','payment','history','schedule');}
 public function store(CheckoutRequest $r,CheckoutService $service){
  $order=$service->checkout($r->user()->id,$r->validated());
  try {event('meal.order.placed',[$order->id]);} catch(\Throwable $e){report($e);}
  return response()->json($order,201);
 }
 public function adminIndex(){return Order::with('items','payment','history','schedule','user:id,name')->latest()->get();}
 public function status(Request $r,Order $order,CheckoutService $service){
  $data=$r->validate(['status'=>'required|in:confirmed,processing,ready,completed,cancelled','reason'=>'nullable|string|max:500']);
  return $service->transition($order,$data['status'],$data['reason']??null,$r->user()->id);
 }
 public function payment(Request $r,Order $order,CheckoutService $service){
  $data=$r->validate(['status'=>'required|in:unpaid,waiting_confirmation,paid,rejected']);
  return $service->payment($order,$data['status']);
 }
 public function customers(){
  return User::where('role','customer')->select('id','name','email','phone','created_at')->withCount('orders')->orderBy('name')->get();
 }
 private function completed(Request $r){
  $d=$r->validate(['from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d'.($r->filled('from')?'|after_or_equal:from':'')]);
  $q=Order::where('status','completed')->whereHas('payment',fn($q)=>$q->where('status','paid'));
  if(!empty($d['from']))$q->whereDate('updated_at','>=',$d['from']);
  if(!empty($d['to']))$q->whereDate('updated_at','<=',$d['to']);
  return $q;
 }
 public function report(Request $r){
  $q=$this->completed($r);
  $rows=(clone $q)->orderByDesc('updated_at')->get(['order_code','total_amount','updated_at','fulfillment']);
  $daily=$rows->groupBy(fn($o)=>$o->updated_at->format('Y-m-d'))->map(fn($items)=>$items->sum('total_amount'))->sortKeys();
  return ['completed_orders'=>$rows->count(),'sales'=>(clone $q)->sum('total_amount'),'customers'=>User::where('role','customer')->count(),'daily'=>$daily,'transactions'=>$rows];
 }
 public function export(Request $r){
  $q=$this->completed($r);
  return response()->streamDownload(function()use($q){
   $f=fopen('php://output','w');fputcsv($f,['Kode','Waktu selesai','Metode','Total'],',','"','');
   foreach($q->orderBy('id')->cursor() as $o) fputcsv($f,[$o->order_code,$o->updated_at->toDateTimeString(),$o->fulfillment,$o->total_amount],',','"','');
   fclose($f);
  },'mealorder-transactions.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
 }
}
