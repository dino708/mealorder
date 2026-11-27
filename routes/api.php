<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\{Product,Category,PreorderSchedule};
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Admin\CrudController;
Route::get('/ping',fn()=>['ok'=>true]);
Route::get('/settings',fn()=>['delivery_enabled'=>config('meal.delivery_enabled'),'delivery_fee'=>max(0,(int)config('meal.delivery_fee'))]);
Route::get('/catalog',fn()=>Product::with('category')->where('is_available',true)->orderBy('name')->get());
Route::post('/quote',function(Request $r,\App\Services\QuoteService $quote){
    $data=$r->validate(['items'=>'required|array|min:1|max:50','items.*.product_id'=>'required|integer|distinct|exists:products,id','items.*.quantity'=>'required|integer|min:1|max:100']);
    return $quote->estimate($data['items']);
})->middleware('throttle:30,1');
Route::get('/categories',fn()=>Category::orderBy('name')->get());
Route::get('/schedules',fn()=>PreorderSchedule::where('status','open')->where('start_at','<=',now())->where('end_at','>',now())->where(fn($q)=>$q->whereNull('fulfillment_at')->orWhere('fulfillment_at','>',now()))->get());
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/me',fn(Request $r)=>$r->user());
    Route::get('/orders/{order}',[OrderController::class,'show']);
    Route::middleware('role:customer')->group(function(){
        Route::get('/orders',[OrderController::class,'index']);
        Route::post('/orders',[OrderController::class,'store']);
    });
    Route::prefix('admin')->middleware('role:admin')->group(function(){
        Route::get('/orders',[OrderController::class,'adminIndex']);
        Route::patch('/orders/{order}/status',[OrderController::class,'status']);
        Route::patch('/orders/{order}/payment',[OrderController::class,'payment']);
        Route::get('/report',[OrderController::class,'report']);
        Route::get('/customers',[OrderController::class,'customers']);
        Route::get('/report/export',[OrderController::class,'export']);
        Route::get('/{type}',[CrudController::class,'index'])->whereIn('type',['categories','products','schedules']);
        Route::post('/{type}',[CrudController::class,'store'])->whereIn('type',['categories','products','schedules']);
        Route::put('/{type}/{id}',[CrudController::class,'update'])->whereIn('type',['categories','products','schedules'])->whereNumber('id');
        Route::delete('/{type}/{id}',[CrudController::class,'destroy'])->whereIn('type',['categories','products','schedules'])->whereNumber('id');
    });
});
