<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderStatusHistory extends Model {
 public $timestamps=false;
 protected $fillable=['actor_id','status','reason','created_at'];
 protected $casts=['created_at'=>'datetime'];
}
