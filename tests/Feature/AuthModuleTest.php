<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_is_redirected_to_dashboard(): void
    {
        $this->seed(RbacSeeder::class);
        $token = 'test-token';

        $user = User::factory()->create(['password' => 'password']);
        $user->assignRole(Roles::ADMIN);

        $this->withSession(['_token' => $token])->post(route('login.store'), [
            '_token' => $token,
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_credentials_are_rejected(): void
    {
        $token = 'test-token';

        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->withSession(['_token' => $token])->from(route('login'))->post(route('login.store'), [
            '_token' => $token,
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $token = 'test-token';
        $user = User::factory()->create();

        $this->withSession(['_token' => $token])
            ->actingAs($user)
            ->post(route('logout'), ['_token' => $token])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
