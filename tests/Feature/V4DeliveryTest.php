<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Category,Product,PreorderSchedule};
class V4DeliveryTest extends TestCase {
 use RefreshDatabase;
 public function test_delivery_fee_and_status_history(): void {
  config(['meal.delivery_fee'=>5000,'meal.delivery_enabled'=>true]);
  $u=User::factory()->create();$a=User::factory()->create();$a->forceFill(['role'=>'admin'])->save();
  $c=Category::create(['name'=>'Makanan']);$p=Product::create(['name'=>'Nasi','category_id'=>$c->id,'price'=>10000,'stock'=>5]);
  $s=PreorderSchedule::create(['title'=>'PO','start_at'=>now()->subHour(),'end_at'=>now()->addHour(),'fulfillment_at'=>now()->addHours(2),'status'=>'open','quota'=>10]);
  $body=['schedule_id'=>$s->id,'items'=>[['product_id'=>$p->id,'quantity'=>2]],'fulfillment'=>'delivery','payment_method'=>'cash'];
  $this->actingAs($u)->postJson('/api/orders',$body)->assertUnprocessable();
  $id=$this->postJson('/api/orders',$body+['delivery_address'=>'Jalan Demo 1','contact_phone'=>'08123456789','delivery_fee'=>1])->assertCreated()->assertJsonPath('total_amount','25000.00')->json('id');
  $this->actingAs($a)->patchJson("/api/admin/orders/$id/status",['status'=>'confirmed'])->assertOk();
  $this->patchJson("/api/admin/orders/$id/status",['status'=>'processing'])->assertOk();
  $this->patchJson("/api/admin/orders/$id/status",['status'=>'on_delivery'])->assertUnprocessable();
  $this->patchJson("/api/admin/orders/$id/status",['status'=>'ready'])->assertOk();
  $this->patchJson("/api/admin/orders/$id/payment",['status'=>'paid'])->assertOk();
  $this->patchJson("/api/admin/orders/$id/status",['status'=>'completed'])->assertOk();
  $this->assertDatabaseCount('order_status_histories',5);
  $this->getJson('/api/admin/customers')->assertOk()->assertJsonMissingPath('0.password');
  $this->getJson('/api/admin/report')->assertOk()->assertJsonPath('completed_orders',1);
  $this->actingAs($u)->getJson("/api/orders/$id")->assertOk()->assertJsonPath('status','completed');
 }
}
