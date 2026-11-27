<?php
namespace App\Services;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
class QuoteService {
    public function estimate(array $items): array {
        $products=Product::whereIn('id',array_column($items,'product_id'))->where('is_available',true)->get()->keyBy('id');
        $payload=[];$cents=0;
        foreach($items as $item){
            $p=$products->get($item['product_id']);
            if(!$p)throw ValidationException::withMessages(['items'=>'Produk tidak tersedia.']);
            $payload[]=['price'=>$p->price,'quantity'=>(int)$item['quantity']];
            $cents+=(int)str_replace('.','',$p->price)*$item['quantity'];
        }
        $url=config('meal.quote_url');
        if($url){
            $r=Http::timeout(5)->post($url,['items'=>$payload])->throw()->json();
            // Function URL unwraps the response body; direct test wrappers may not.
            if(isset($r['body'])&&is_string($r['body']))$r=json_decode($r['body'],true);
            return ['estimate'=>$r['estimate'],'authoritative'=>false,'source'=>'lambda'];
        }
        return ['estimate'=>intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT),'authoritative'=>false,'source'=>'local'];
    }
}
