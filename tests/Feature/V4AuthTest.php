<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class V4AuthTest extends TestCase {
 use RefreshDatabase;
 public function test_registration_ignores_requested_admin_role(): void {
  $this->post('/register',['name'=>'Demo','email'=>'new@example.test','password'=>'LongDemo123!','password_confirmation'=>'LongDemo123!','role'=>'admin'])->assertRedirect('/dashboard');
  $u=User::where('email','new@example.test')->firstOrFail();
  $this->assertSame('customer',$u->role);
  $this->assertTrue(Hash::check('LongDemo123!',$u->password));
  $this->get('/admin')->assertForbidden();
 }
 public function test_login_rejects_wrong_password_and_accepts_correct_password(): void {
  $u=User::factory()->create(['password'=>'DemoPass123!']);
  $this->post('/login',['email'=>$u->email,'password'=>'wrong'])->assertSessionHasErrors('email');
  $this->assertGuest();
  $this->post('/login',['email'=>$u->email,'password'=>'DemoPass123!'])->assertRedirect('/dashboard');
  $this->assertAuthenticatedAs($u);
 }
}
