<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model {
    use SoftDeletes;
    protected $fillable=['category_id','name','description','price','stock','image','is_available'];
    protected $casts=['price'=>'decimal:2','is_available'=>'boolean']; public function category(){return $this->belongsTo(Category::class);}
}
