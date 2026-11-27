<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
return new class extends Migration {
    public function up(): void {
        Schema::table('products',fn(Blueprint $t)=>$t->softDeletes());
        Schema::table('order_items',fn(Blueprint $t)=>$t->string('product_image',255)->nullable());
        // Normalize local legacy paths and preserve the best available photo for existing receipts.
        DB::table('products')->orderBy('id')->chunkById(200,function($rows){
            foreach($rows as $r){
                $path=$r->image;
                if($path) {
                    $path=preg_replace('~^/?storage/~','',$path);
                    $path=ltrim($path,'/');
                    $path=['images/demo-nasi-ayam.svg'=>'images/menu/nasi-ayam.webp','images/demo-nasi-telur.svg'=>'images/menu/nasi-telur.webp','images/demo-es-teh.svg'=>'images/menu/es-teh.webp'][$path]??$path;
                    if(!preg_match('~^(?:meal-images|images)/[a-zA-Z0-9_./-]+$~D',$path)||str_contains($path,'..'))$path=null;
                }
                DB::table('products')->where('id',$r->id)->update(['image'=>$path]);
                DB::table('order_items')->where('product_id',$r->id)->update(['product_image'=>$path]);
            }
        });
        DB::table('orders')->where('status','on_delivery')->update(['status'=>'ready']);
        DB::table('order_status_histories')->where('status','on_delivery')->update(['status'=>'ready']);
        // Exact known legacy seed labels only; preserve IDs, passwords, stock and order snapshots.
        foreach(['Nasi Ayam Demo'=>'Nasi Ayam','Nasi Telur Demo'=>'Nasi Telur','Es Teh Demo'=>'Es Teh Manis'] as $old=>$new){
            DB::table('products')->where('name',$old)->update(['name'=>$new]);
            DB::table('order_items')->where('product_name',$old)->update(['product_name'=>$new]);
        }
        DB::table('users')->where('email','admin@example.test')->where('name','Admin Demo')->update(['name'=>'Pengelola MealOrder']);
        DB::table('users')->where('email','customer@example.test')->where('name','Pelanggan Demo')->update(['name'=>'Pelanggan MealOrder']);
        DB::table('preorder_schedules')->where('title','PO Demo')->update(['title'=>'Pre-order Makan Siang']);
    }
    public function down(): void {
        if(DB::table('products')->whereNotNull('deleted_at')->exists()) throw new \RuntimeException('Pulihkan data arsip sebelum rollback.');
        Schema::table('order_items',fn(Blueprint $t)=>$t->dropColumn('product_image'));
        Schema::table('products',fn(Blueprint $t)=>$t->dropSoftDeletes());
    }
};
