<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Repositories\StripeWebhookRepository;
use App\Services\StripeWebhookService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class StripeWebhookServiceTest extends TestCase
{
    public function test_checkout_session_completed_creates_payment_when_missing(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $order = new Order();
        $order->id = 42;
        $order->user_id = null;

        $repo = Mockery::mock(StripeWebhookRepository::class);
        $repo->shouldReceive('findOrderByNumber')
            ->once()
            ->with('ORD-4242')
            ->andReturn($order);

        $repo->shouldReceive('markOrderPaid')
            ->once()
            ->with($order, 'cs_test_4242', 'pi_test_4242');

        $repo->shouldReceive('findPaymentByOrderId')
            ->once()
            ->with(42)
            ->andReturn(null);

        $repo->shouldReceive('createPayment')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['order_id'] === 42
                    && $data['payment_status'] === 'paid'
                    && $data['payment_method'] === 'stripe'
                    && $data['stripe_payment_intent_id'] === 'pi_test_4242';
            }));

        $payload = json_encode([
            'id' => 'evt_test_4242',
            'object' => 'event',
            'api_version' => '2025-03-31.basil',
            'created' => time(),
            'livemode' => false,
            'pending_webhooks' => 1,
            'request' => ['id' => null, 'idempotency_key' => null],
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_4242',
                    'object' => 'checkout.session',
                    'payment_intent' => 'pi_test_4242',
                    'payment_status' => 'paid',
                    'amount_total' => 12500,
                    'customer' => 'cus_test_4242',
                    'metadata' => [
                        'order_number' => 'ORD-4242',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = $this->buildStripeSignatureHeader($payload, 'whsec_test_secret');

        $request = Request::create('/stripe/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('Stripe-Signature', $signature);

        $response = (new StripeWebhookService($repo))->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"status":"success"}', $response->getContent());
    }

    public function test_invalid_signature_returns_403(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $repo = Mockery::mock(StripeWebhookRepository::class);
        $repo->shouldNotReceive('findOrderByNumber');

        $payload = json_encode([
            'id' => 'evt_test_invalid',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['metadata' => ['order_number' => 'ORD-FAIL']]],
        ], JSON_THROW_ON_ERROR);

        $request = Request::create('/stripe/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('Stripe-Signature', 't=123,v1=invalid');

        $response = (new StripeWebhookService($repo))->handle($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('{"status":"error","message":"Invalid signature."}', $response->getContent());
    }

    private function buildStripeSignatureHeader(string $payload, string $secret): string
    {
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return 't=' . $timestamp . ',v1=' . $signature;
    }
}
