<?php
namespace App\Services;
use Stripe\Stripe;
use App\Repositories\UserAddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Wishlist;
use Stripe\Refund;
use App\Models\Order;
use App\Jobs\SendOrderStatusNotificationJob;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminPartPaymentService{
      use AuthorizesRequests;
       protected $siteTitle;
    public function __construct(protected ProductService $product_service)
    {
        $this->siteTitle = '';
    }
    public function markFailedService($validator, $id){
          $payment = Payment::findOrFail($id);
            $payment->payment_status = 'failed';
        if ($validator->notes) {
            $payment->notes = $validator->notes;
        }
        $payment->save();
        return $payment->payment_status;
    }
    public function processService($id,$payment){


        // Pull the related order first to sync identifiers
        $order = $payment->order;

        // Mark payment completed and sync Stripe PaymentIntent ID from order if available
        $payment->payment_status = 'completed';
        $payment->paid_at = now();
        if ($order && empty($payment->stripe_payment_intent_id) && !empty($order->stripe_payment_intent_id)) {
            $payment->stripe_payment_intent_id = $order->stripe_payment_intent_id;
        }
        $payment->save();

        // Update order payment status
        if ($order) {
            $order->payment_status = 'paid';
            $order->save();
        }
        return $order->payment_status;
    }
    public function refundService(array $validator,Payment $payment){

        $order = $payment->order;
        if ($payment->payment_status != 'completed') {
            return [
                'success' => false,
                'message' => 'Only completed payments can be refunded!'
            ];
        }

        if ($validator['refund_amount'] > $payment->amount) {
            return [
                'success' => false,
                'message' => 'Refund amount cannot be greater than payment amount!'
            ];
        }
        Stripe::setApiKey(config('services.stripe.secret'));
        try{
             $refund = Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => (int) ($validator['refund_amount'] * 100),
            ]);
            // Save refund id and mark status pending until finalized
           $payment->update([
            'payment_status' => 'refund_pending',
            'stripe_refund_id' => $refund->id ?? null,
            'notes' => $validator['notes'],
        ]);

        // Update order payment status if fully refunded
        if ($validator['refund_amount'] == $payment->amount && $payment->order) {

            if ($order) {
                $order->payment_status = 'unpaid';
                $order->save();
            }
        }

        return [
            'success' => true,
            'message' => 'Payment refunded successfully',
            'payment_status' => $payment->payment_status,
        ];

        }catch(\Exception $e){
            return [
            'success' => false,
            'message' => 'Stripe refund failed: ' . $e->getMessage()
            ];
        }
    }

    public function updateNotesService($validator, $id){
        $payment = Payment::findOrFail($id);
        $payment->notes = $validator->notes;
        $payment->save();

          return [
            'success' => true,
            'message' => 'Payment notes updated successfully',
        ];
    }

    public function getStatisticsService()
    {
        $today = Carbon::now()->format('Y-m-d');
        $thisMonth = Carbon::now()->format('Y-m');

        $statistics = [
            'total_payments' => Payment::count(),
            'completed_payments' => Payment::where('payment_status', 'completed')->count(),
            'pending_payments' => Payment::where('payment_status', 'pending')->count(),
            'failed_payments' => Payment::where('payment_status', 'failed')->count(),
            'refunded_payments' => Payment::where('payment_status', 'refunded')->count(),
            'total_amount' => Payment::sum('amount'),
            'completed_amount' => Payment::where('payment_status', 'completed')->sum('amount'),
            'pending_amount' => Payment::where('payment_status', 'pending')->sum('amount'),
            'today_amount' => Payment::whereDate('created_at', $today)->sum('amount'),
            'month_amount' => Payment::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->sum('amount'),
        ];

        return $statistics;
    }
     public function getTrendsService()
    {
        $trends = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            $label = $date->format('M Y');

            $completed = Payment::where('payment_status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $failed = Payment::where('payment_status', 'failed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $trends[] = [
                'month' => $label,
                'completed' => (float)$completed,
                'failed' => (float)$failed,
            ];
        }

        return $trends;
    }
      public function getMethodBreakdownService()
    {
        $methods = ['cod', 'card', 'bkash', 'stripe'];
        $breakdown = [];

        foreach ($methods as $method) {
            $count = Payment::where('payment_method', $method)->count();
            $amount = Payment::where('payment_method', $method)->sum('amount');

            $breakdown[] = [
                'method' => strtoupper($method),
                'count' => $count,
                'amount' => (float)$amount,
            ];
        }

        return $breakdown;
    }

}
