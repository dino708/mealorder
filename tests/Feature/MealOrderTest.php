<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Product,Category,PreorderSchedule};
class MealOrderTest extends TestCase {
    use RefreshDatabase;
    private function setupOrder(): array {
        $user=User::factory()->create();
        $cat=Category::create(['name'=>'Test']);
        $p=Product::create(['category_id'=>$cat->id,'name'=>'Nasi','price'=>10000,'stock'=>5]);
        $s=PreorderSchedule::create(['title'=>'PO','start_at'=>now()->subHour(),'end_at'=>now()->addHour(),'quota'=>3,'status'=>'open']);
        return [$user,$p,$s];
    }
    public function test_checkout_price_quota_and_cancel_once(): void {
        [$u,$p,$s]=$this->setupOrder();
        $payload=['schedule_id'=>$s->id,'items'=>[['product_id'=>$p->id,'quantity'=>2,'price'=>1]]];
        $this->actingAs($u)->postJson('/api/orders',$payload)->assertCreated()->assertJsonPath('total_amount','20000.00');
        $order=$u->orders()->firstOrFail();
        $this->assertSame(3,$p->fresh()->stock);
        $this->postJson('/api/orders',$payload)->assertUnprocessable();
        $this->actingAs(User::factory()->create())->getJson('/api/orders/'.$order->id)->assertForbidden();
        $admin=User::factory()->create();$admin->forceFill(['role'=>'admin'])->save();
        $this->actingAs($admin)->patchJson('/api/admin/orders/'.$order->id.'/status',['status'=>'cancelled','reason'=>'Stok bahan habis'])->assertOk();
        $this->patchJson('/api/admin/orders/'.$order->id.'/status',['status'=>'cancelled','reason'=>'Stok bahan habis'])->assertOk();
        $this->assertSame(5,$p->fresh()->stock);
        $this->actingAs($u)->postJson('/api/orders',$payload)->assertCreated();
    }
    public function test_guests_and_customers_cannot_access_admin(): void {
        $this->getJson('/api/orders')->assertUnauthorized();
        $this->actingAs(User::factory()->create())->getJson('/api/admin/orders')->assertForbidden();
    }
    public function test_closed_period_rejected(): void {
        [$u,$p,$s]=$this->setupOrder();$s->update(['status'=>'closed']);
        $this->actingAs($u)->postJson('/api/orders',['schedule_id'=>$s->id,'items'=>[['product_id'=>$p->id,'quantity'=>1]]])->assertUnprocessable();
        $this->assertSame(5,$p->fresh()->stock);
    }
}
