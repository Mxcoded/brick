<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfilePasswordChangeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
            'password' => 'password',
        ]);
    }

    #[Test]
    public function changing_password_succeeds_and_persists_the_new_hash(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'password',
            'new_password' => 'newsecret123',
            'new_password_confirmation' => 'newsecret123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
        $this->assertTrue(Auth::check(), 'User should remain logged in after changing their own password.');
    }

    #[Test]
    public function wrong_current_password_returns_an_inline_validation_error(): void
    {
        $user = $this->makeUser();

        $response = $this->from(route('profile.show'))
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'wrong-password',
                'new_password' => 'newsecret123',
                'new_password_confirmation' => 'newsecret123',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    #[Test]
    public function profile_page_renders_the_password_form_and_is_linked_from_the_layout(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee('current_password');
        $response->assertSee('new_password_confirmation');
        $response->assertSee(route('profile.show'));
    }

    #[Test]
    public function mismatched_confirmation_returns_an_inline_validation_error(): void
    {
        $user = $this->makeUser();

        $response = $this->from(route('profile.show'))
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'new_password' => 'newsecret123',
                'new_password_confirmation' => 'different123',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('new_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    #[Test]
    public function password_shorter_than_eight_characters_is_rejected(): void
    {
        $user = $this->makeUser();

        $response = $this->from(route('profile.show'))
            ->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'new_password' => 'short1',
                'new_password_confirmation' => 'short1',
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHasErrors('new_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
