<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{User,Category,Product,PreorderSchedule};
class DatabaseSeeder extends Seeder {
    public function run(): void {
        if (!app()->environment('local','testing')) return;
        if (config('meal.seed_dev_accounts')) {
            $admin=User::firstOrCreate(['email'=>'admin@example.test'],['name'=>'Pengelola MealOrder','password'=>'DemoOnly123!']);
            $admin->forceFill(['role'=>'admin'])->save();
            User::firstOrCreate(['email'=>'customer@example.test'],['name'=>'Pelanggan MealOrder','password'=>'DemoOnly123!']);
        }
        if (!config('meal.seed_catalog')) return;
        foreach ([
            ['nasi-ayam','Nasi Ayam','Makanan Utama','Nasi putih, ayam bumbu kecap, lalapan dan sambal.',15000,30],
            ['nasi-telur','Nasi Telur','Makanan Utama','Nasi hangat, telur mata sapi dan sambal.',10000,30],
            ['nasi-goreng','Nasi Goreng','Makanan Utama','Nasi goreng kecap, sayuran dan telur mata sapi.',18000,25],
            ['ayam-geprek','Ayam Geprek','Makanan Utama','Ayam renyah dengan sambal cabai dan nasi.',17000,25],
            ['mie-goreng','Mie Goreng','Makanan Utama','Mie goreng dengan sawi, wortel dan daun bawang.',14000,25],
            ['sate-ayam','Sate Ayam','Makanan Utama','Sate ayam bumbu kacang dan irisan mentimun.',20000,20],
            ['risol-mayo','Risol Mayo','Makanan Ringan','Tiga risol renyah dengan isian telur dan mayones.',12000,24],
            ['tahu-isi','Tahu Isi','Makanan Ringan','Tiga tahu goreng dengan isian sayur.',9000,24],
            ['es-teh','Es Teh Manis','Minuman','Teh manis dingin dengan es batu.',5000,50],
            ['es-jeruk','Es Jeruk','Minuman','Minuman jeruk segar dengan es batu.',8000,40],
            ['paket-ayam','Paket Nasi Ayam','Paket Hemat','Nasi ayam, lalapan dan es teh manis.',19000,20],
            ['puding-cokelat','Puding Cokelat','Dessert','Puding cokelat lembut dengan saus susu.',10000,20],
        ] as [$slug,$name,$category,$description,$price,$stock]) {
            $cat=Category::firstOrCreate(['name'=>$category]);
            // Repeated seeding must not reset stock, price or restore archived products.
            $product=Product::withTrashed()->firstOrCreate(['name'=>$name],[
                'category_id'=>$cat->id,'description'=>$description,'price'=>$price,
                'stock'=>$stock,'image'=>'images/menu/'.$slug.'.webp','is_available'=>true]);
            if (!$product->trashed() && (!$product->image || str_contains($product->image,'images/demo-'))) {
                $product->update(['image'=>'images/menu/'.$slug.'.webp']);
            }
        }
        PreorderSchedule::firstOrCreate(['title'=>'Pre-order Makan Siang'],[
            'start_at'=>now()->subHour(),'end_at'=>now()->addDays(7),'quota'=>100,
            'status'=>'open','fulfillment_at'=>now()->addDays(8)]);
    }
}
