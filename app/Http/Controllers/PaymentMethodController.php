<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentMethod as StripePaymentMethod;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Display user's saved payment methods
     */
    public function index()
    {
        $paymentMethods = UserPaymentMethod::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend_view.pages.payment_methods.index', [
            'data' => [
                'title' => 'Saved Payment Methods',
            ],
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Set a payment method as default
     */
    public function setDefault($id)
    {
        // Remove default from all user's payment methods
        UserPaymentMethod::where('user_id', Auth::id())->update(['is_default' => false]);

        // Set this one as default
        $paymentMethod = UserPaymentMethod::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $paymentMethod->is_default = true;
        $paymentMethod->save();

        return back()->with('success', 'Default payment method updated successfully.');
    }

    /**
     * Delete a saved payment method
     */
    public function destroy($id)
    {
        $paymentMethod = UserPaymentMethod::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Also delete from Stripe
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            StripePaymentMethod::retrieve($paymentMethod->stripe_payment_method_id)
                ->detach();
        } catch (\Exception $e) {
            // Log but continue if Stripe deletion fails
            Log::warning('Failed to detach payment method from Stripe: ' . $e->getMessage());
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method removed successfully.');
    }

    /**
     * Get saved payment methods as JSON (for checkout)
     */
    public function getSavedMethods()
    {
        $paymentMethods = UserPaymentMethod::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($pm) {
                return [
                    'id' => $pm->id,
                    'stripe_payment_method_id' => $pm->stripe_payment_method_id,
                    'display' => $pm->card_display,
                    'brand' => $pm->card_brand,
                    'last4' => $pm->card_last4,
                    'exp_month' => $pm->card_exp_month,
                    'exp_year' => $pm->card_exp_year,
                    'is_default' => $pm->is_default,
                    'is_expired' => $pm->isExpired(),
                ];
            });

        return response()->json($paymentMethods);
    }
}
