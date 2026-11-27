<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Category,Product,PreorderSchedule};
class OrderFlowTest extends TestCase {
    use RefreshDatabase;
    public function test_order_requires_payment_before_completion(): void {
        $customer = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role'=>'admin'])->save();
        $category = Category::create(['name'=>'Makanan']);
        $product = Product::create(['category_id'=>$category->id,'name'=>'Nasi','price'=>15000,'stock'=>5]);
        $schedule = PreorderSchedule::create(['title'=>'PO','start_at'=>now()->subHour(),'end_at'=>now()->addHour(),'quota'=>10,'status'=>'open']);
        $id = $this->actingAs($customer)->postJson('/api/orders',[
            'schedule_id'=>$schedule->id,'items'=>[['product_id'=>$product->id,'quantity'=>1]]
        ])->assertCreated()->json('id');
        $this->actingAs($admin);
        $this->patchJson("/api/admin/orders/$id/status",['status'=>'ready'])->assertUnprocessable();
        foreach (['confirmed','processing','ready'] as $status) {
            $this->patchJson("/api/admin/orders/$id/status",['status'=>$status])->assertOk()->assertJsonPath('status',$status);
        }
        $this->patchJson("/api/admin/orders/$id/status",['status'=>'completed'])->assertUnprocessable();
        $this->patchJson("/api/admin/orders/$id/payment",['status'=>'paid'])->assertOk();
        $this->patchJson("/api/admin/orders/$id/status",['status'=>'completed'])->assertOk();
        $this->getJson('/api/admin/report')->assertOk()->assertJsonPath('completed_orders',1);
        $this->actingAs($customer)->getJson("/api/orders/$id")->assertOk()->assertJsonPath('status','completed');
    }
}
