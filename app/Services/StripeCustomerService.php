<?php
namespace App\Services;

use App\Repositories\PaymentProcessRepository;
use App\Repositories\CheckoutRepository;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class StripeCustomerService
{
    public function __construct(
        protected PaymentProcessRepository $payment_repo,
        protected CheckoutRepository $checkout_repo,
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Ensure a Stripe customer exists for the given user and return its ID.
     *
     * @param \App\Models\User $user
     * @return string Stripe customer ID
     * @throws \Exception if user is not provided
     */
    public function ensureCustomerExists($user): string
    {
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        // Return existing customer ID if available
        if (!empty($user->stripe_customer_id)) {
            return $user->stripe_customer_id;
        }

        // Create new Stripe customer
        $customer = StripeCustomer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'app_user_id' => $user->id,
            ],
        ]);

        // Save customer ID to user record
        $this->payment_repo->saveUserStripeCustomerId($user, $customer->id);

        return $customer->id;
    }

    /**
     * Sync user data to Stripe customer (for future use).
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function syncCustomerData($user): void
    {
        if (!$user || !$user->stripe_customer_id) {
            return;
        }

        StripeCustomer::update($user->stripe_customer_id, [
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    /**
     * Save payment method from a PaymentIntent (fallback when webhook hasn't fired).
     *
     * @param string $paymentIntentId
     * @param int $userId
     * @return void
     */
    public function savePaymentMethodFromIntent(string $paymentIntentId, int $userId): void
    {
        if (!$paymentIntentId || !$userId) {
            return;
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if (empty($paymentIntent->payment_method)) {
                return;
            }

            $paymentMethodId = $paymentIntent->payment_method;

            // Already saved?
            if ($this->checkout_repo->savedPaymentMethodExists($userId, $paymentMethodId)) {
                return;
            }

            $stripeMethod = PaymentMethod::retrieve($paymentMethodId);

            if ($stripeMethod->type === 'card') {
                $isFirst = $this->checkout_repo->countSavedPaymentMethods($userId) === 0;

                $this->checkout_repo->createSavedPaymentMethod([
                    'user_id' => $userId,
                    'stripe_payment_method_id' => $paymentMethodId,
                    'card_brand' => $stripeMethod->card->brand,
                    'card_last4' => $stripeMethod->card->last4,
                    'card_exp_month' => $stripeMethod->card->exp_month,
                    'card_exp_year' => $stripeMethod->card->exp_year,
                    'is_default' => $isFirst,
                ]);

                Log::info('Payment method saved via success fallback', [
                    'user_id' => $userId,
                    'payment_method_id' => $paymentMethodId,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Fallback save payment method failed: ' . $e->getMessage());
        }
    }
}
