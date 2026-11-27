<?php
namespace Tests\Feature;

use App\Models\{Category, Product, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPhotoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();
        return $user;
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'meal.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAIAAAD8GO2jAAAALUlEQVR4nGM86S3KQEvARFPTRy0YtWDUglELRi0YtWDUglELRi0YtWDUAioCAKCDAWn5oUvLAAAAAElFTkSuQmCC')
        );
    }

    private function payload(): array
    {
        return ['category_id' => Category::create(['name' => 'Makanan'])->id,
            'name' => 'Nasi Ayam', 'price' => '15000', 'stock' => '10',
            'is_available' => '1'];
    }

    public function test_admin_can_create_and_replace_photo(): void
    {
        Storage::fake('public');
        $payload = $this->payload();
        $response = $this->actingAs($this->admin())
            ->post('/api/admin/products', $payload + ['image_file' => $this->photo()], ['Accept' => 'application/json'])
            ->assertCreated();
        $id = $response->json('id');
        $first = $response->json('image');
        $this->assertStringStartsWith('meal-images/', $first);
        Storage::disk('public')->assertExists($first);

        $update = $this->post("/api/admin/products/$id",
            $payload + ['_method' => 'PUT', 'image_file' => $this->photo()],
            ['Accept' => 'application/json'])->assertOk();
        $second = $update->json('image');
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        $this->assertSame($second, Product::findOrFail($id)->image);
        Storage::disk('public')->assertMissing($first);
        $this->putJson("/api/admin/products/$id",$payload+['description'=>'Baru'])->assertOk()->assertJsonPath('image',$second);
        Storage::disk('public')->assertExists($second);
        $this->deleteJson("/api/admin/products/$id")->assertNoContent();
        Storage::disk('public')->assertMissing($second);
    }

    public function test_invalid_file_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post('/api/admin/products',
            $this->payload() + ['image_file' => UploadedFile::fake()->createWithContent('wrong.txt', 'not an image')],
            ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('image_file');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_oversized_file_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post('/api/admin/products',
            $this->payload()+['image_file'=>$this->photo()->size(5121)],
            ['Accept'=>'application/json'])->assertUnprocessable()->assertJsonValidationErrors('image_file');
        $this->assertDatabaseCount('products',0);
    }

    public function test_customer_cannot_upload(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create())->post('/api/admin/products',
            $this->payload() + ['image_file' => $this->photo()], ['Accept' => 'application/json'])
            ->assertForbidden();
        $this->assertDatabaseCount('products', 0);
    }

    public function test_history_keeps_photo_and_archived_product(): void
    {
        Storage::fake('public');
        $admin=$this->admin(); $customer=User::factory()->create();
        $payload=$this->payload();
        $created=$this->actingAs($admin)->post('/api/admin/products',$payload+['image_file'=>$this->photo()],['Accept'=>'application/json'])->assertCreated();
        $id=$created->json('id'); $image=$created->json('image');
        $schedule=\App\Models\PreorderSchedule::create(['title'=>'Makan siang','start_at'=>now()->subHour(),'end_at'=>now()->addHour(),'quota'=>20,'status'=>'open']);
        $order=$this->actingAs($customer)->postJson('/api/orders',['schedule_id'=>$schedule->id,'items'=>[['product_id'=>$id,'quantity'=>1]]])->assertCreated()->json('id');
        $this->actingAs($admin)->post("/api/admin/products/$id",array_merge($payload,['_method'=>'PUT','stock'=>9,'image_file'=>$this->photo()]),['Accept'=>'application/json'])->assertOk();
        Storage::disk('public')->assertExists($image);
        $this->deleteJson("/api/admin/products/$id")->assertNoContent();
        $this->assertSoftDeleted('products',['id'=>$id]);
        $this->getJson('/api/catalog')->assertExactJson([]);
        $this->actingAs($customer)->getJson("/api/orders/$order")->assertOk()->assertJsonPath('items.0.product_image',$image);
        Storage::disk('public')->assertExists($image);
        $this->actingAs($admin)->patchJson("/api/admin/orders/$order/status",['status'=>'cancelled','reason'=>'Pesanan dibatalkan'])->assertOk();
        $this->assertSame(10,Product::withTrashed()->findOrFail($id)->stock);
    }

    public function test_disguised_extension_and_arbitrary_paths_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $payload=$this->payload();
        $bad=UploadedFile::fake()->createWithContent('meal.php',file_get_contents($this->photo()->getPathname()));
        $this->post('/api/admin/products',$payload+['image_file'=>$bad],['Accept'=>'application/json'])->assertUnprocessable();
        $this->postJson('/api/admin/products',$payload+['image'=>'../../.env'])->assertUnprocessable();
        $this->assertDatabaseCount('products',0);
    }

    public function test_failed_update_removes_new_unreferenced_file(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post('/api/admin/products/99999',$this->payload()+['_method'=>'PUT','image_file'=>$this->photo()],['Accept'=>'application/json'])->assertNotFound();
        $this->assertSame([],Storage::disk('public')->allFiles('meal-images'));
    }

    public function test_image_signature_without_decodable_pixels_is_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post('/api/admin/products',
            $this->payload()+['image_file'=>UploadedFile::fake()->createWithContent('meal.png',"\x89PNG\r\n\x1a\n<?php echo 1; ?>")],
            ['Accept'=>'application/json'])->assertUnprocessable();
        $this->assertSame([],Storage::disk('public')->allFiles('meal-images'));
    }

    public function test_shared_photo_is_not_deleted_while_referenced(): void
    {
        Storage::fake('public');
        $admin=$this->admin();$payload=$this->payload();
        $row=$this->actingAs($admin)->post('/api/admin/products',$payload+['image_file'=>$this->photo()],['Accept'=>'application/json'])->assertCreated()->json();
        $second=Product::create(array_merge($payload,['name'=>'Menu lain','image'=>$row['image']]));
        $this->deleteJson('/api/admin/products/'.$row['id'])->assertNoContent();
        Storage::disk('public')->assertExists($row['image']);
        $this->deleteJson('/api/admin/products/'.$second->id)->assertNoContent();
        Storage::disk('public')->assertMissing($row['image']);
    }
}
