<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::ADMIN);

        $this->actingAs($this->admin);
    }

    public function test_admin_can_create_update_and_delete_user(): void
    {
        $token = 'test-token';

        $this->withSession(['_token' => $token])->post(route('users.store'), [
            '_token' => $token,
            'name' => 'User Test',
            'email' => 'user-test@example.com',
            'password' => 'password',
            'roles' => [Roles::STAFF],
        ])
            ->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'user-test@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Roles::STAFF));
        $this->assertTrue(Hash::check('password', $user->password));

        $this->put(route('users.update', $user), [
            '_token' => $token,
            'name' => 'User Test Updated',
            'email' => 'user-test-updated@example.com',
            'password' => null,
            'roles' => [Roles::INVENTORY],
        ])
            ->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('User Test Updated', $user->name);
        $this->assertSame('user-test-updated@example.com', $user->email);
        $this->assertTrue($user->hasRole(Roles::INVENTORY));
        $this->assertTrue(Hash::check('password', $user->password));

        $this->delete(route('users.destroy', $user), ['_token' => $token])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_current_user(): void
    {
        $token = 'test-token';

        $this->withSession(['_token' => $token])
            ->from(route('users.index'))
            ->delete(route('users.destroy', $this->admin), ['_token' => $token])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
