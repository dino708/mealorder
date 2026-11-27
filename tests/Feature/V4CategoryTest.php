<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Category,Product};
class V4CategoryTest extends TestCase {
 use RefreshDatabase;
 public function test_category_in_use_is_protected(): void {
  $a=User::factory()->create();$a->forceFill(['role'=>'admin'])->save();
  $this->actingAs($a);
  $id=$this->postJson('/api/admin/categories',['name'=>'Minuman'])->assertCreated()->json('id');
  $p=$this->postJson('/api/admin/products',['category_id'=>$id,'name'=>'Teh','price'=>5000,'stock'=>5,'is_available'=>true])->assertCreated()->json('id');
  $this->deleteJson('/api/admin/categories/'.$id)->assertStatus(409);
  $this->getJson('/api/catalog')->assertJsonPath('0.name','Teh');
  $this->putJson('/api/admin/products/'.$p,['category_id'=>$id,'name'=>'Teh hangat','price'=>6000,'stock'=>4,'is_available'=>true])->assertOk();
  $this->deleteJson('/api/admin/products/'.$p)->assertNoContent();
  $this->deleteJson('/api/admin/categories/'.$id)->assertNoContent();
 }
}
