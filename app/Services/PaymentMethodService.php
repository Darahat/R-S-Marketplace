<?php
namespace App\Services;

use App\Models\UserPaymentMethod;
use App\Repositories\UserPaymentMethodRepository;
use App\Jobs\DetachStripePaymentMethodJob;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentMethod as StripePaymentMethod;

class PaymentMethodService
{
    public function __construct(private UserPaymentMethodRepository $repo)
    {
    }
    public function handle(string $action, int $userId, UserPaymentMethod $method): void{
        match($action){
            'set_default' => $this->setDefault($userId, $method),
            'delete' => $this->delete($method),
            default => throw new \InvalidArgumentException('Invalid action'),
        };
    }

    private function setDefault(int $userId, UserPaymentMethod $method):void{
        $this->repo->clearDefault($userId);
        $method->update(['is_default' => true]);
    }

    private function delete(UserPaymentMethod $method):void{
        DetachStripePaymentMethodJob::dispatch($method);
        $method->delete();
    }

    public function formatForCheckout($methods){
          return $methods->map(fn ($pm) => [
            'id' => $pm->id,
            'stripe_payment_method_id' => $pm->stripe_payment_method_id,
            'display' => $pm->card_display,
            'brand' => $pm->card_brand,
            'last4' => $pm->card_last4,
            'exp_month' => $pm->card_exp_month,
            'exp_year' => $pm->card_exp_year,
            'is_default' => $pm->is_default,
            'is_expired' => $pm->isExpired(),
        ]);
    }
}
