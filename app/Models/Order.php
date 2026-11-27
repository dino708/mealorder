<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $fillable=['user_id','schedule_id','order_code','total_amount','status','notes','fulfillment','delivery_address','contact_phone','delivery_fee','cancellation_reason'];
    protected $casts=['total_amount'=>'decimal:2','delivery_fee'=>'decimal:2']; public function history(){return $this->hasMany(OrderStatusHistory::class)->orderBy("id");} public function items(){return $this->hasMany(OrderItem::class);} public function payment(){return $this->hasOne(Payment::class);} public function user(){return $this->belongsTo(User::class);} public function schedule(){return $this->belongsTo(PreorderSchedule::class,'schedule_id');}
}
