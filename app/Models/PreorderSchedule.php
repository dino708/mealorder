<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PreorderSchedule extends Model {
    protected $fillable=['title','start_at','end_at','quota','status','fulfillment_at'];
    protected $casts=['start_at'=>'datetime','end_at'=>'datetime','fulfillment_at'=>'datetime']; public function orders(){return $this->hasMany(Order::class);}
}
