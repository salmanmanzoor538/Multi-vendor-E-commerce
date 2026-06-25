<?php

namespace Tests\Unit\Repositories;

use App\Models\Payment;
use App\Models\User;
use App\Repositories\Eloquent\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_payment_by_batch_session_and_payment_intent(): void
    {
        $user = User::factory()->create();
        $batchId = (string) fake()->uuid();

        Payment::query()->create([
            'checkout_batch_id' => $batchId,
            'user_id' => $user->id,
            'provider' => 'stripe',
            'status' => 'pending',
            'currency' => 'USD',
            'amount' => 100,
            'gateway_reference' => 'cs_repo_1',
            'stripe_session_id' => 'cs_repo_1',
            'stripe_payment_intent_id' => 'pi_repo_1',
        ]);

        $repository = new PaymentRepository();

        $byBatch = $repository->findByCheckoutBatchId($batchId);
        $bySession = $repository->findByStripeSessionId('cs_repo_1');
        $byIntent = $repository->findByStripePaymentIntentId('pi_repo_1');

        $this->assertNotNull($byBatch);
        $this->assertNotNull($bySession);
        $this->assertNotNull($byIntent);
        $this->assertSame($byBatch?->id, $bySession?->id);
        $this->assertSame($byBatch?->id, $byIntent?->id);
    }
}
