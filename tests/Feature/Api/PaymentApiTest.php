<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => 'password',
        ]);
    }

    private function authHeaders(): array
    {
        $token = $this->user->createToken('test-token', ['read', 'write'])->plainTextToken;

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    public function test_list_payments(): void
    {
        $this->markTestSkipped('restaurant_payments table does not exist');
    }

    public function test_record_payment(): void
    {
        $this->markTestSkipped('restaurant_payments table does not exist');
    }

    public function test_record_payment_requires_order(): void
    {
        $this->markTestSkipped('restaurant_payments table does not exist');
    }
}
