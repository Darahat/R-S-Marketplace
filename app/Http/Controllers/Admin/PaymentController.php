<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Refund;
class PaymentController extends Controller
{
    protected $page_title;

    public function __construct()
    {
        $this->page_title = "Payment Management";
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order', 'user']);

        // Search by transaction id or order number
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend_panel_view.pages.payments.index', [
            'payments' => $payments,
            'page_title' => $this->page_title,
            'page_header' => 'Payments',
            'paymentStatuses' => ['pending', 'processing', 'completed', 'failed', 'refund_pending', 'refunded'],
            'paymentMethods' => ['cod', 'card', 'bkash', 'stripe'],
        ]);
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        $payment = Payment::with(['order', 'user'])->findOrFail($id);

        return view('backend_panel_view.pages.payments.show', [
            'payment' => $payment,
            'page_title' => $this->page_title,
            'page_header' => 'Payment Details',
            'paymentStatuses' => ['pending', 'processing', 'completed', 'failed', 'refund_pending', 'refunded'],
        ]);
    }

    /**
     * Process a payment
     */
    public function process(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status == 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is already completed!'
            ]);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_status' => $payment->payment_status,
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markFailed(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $payment->payment_status = 'failed';
        if ($request->notes) {
            $payment->notes = $request->notes;
        }
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as failed',
            'payment_status' => $payment->payment_status,
        ]);
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $order = $payment->order;
        if ($payment->payment_status != 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed payments can be refunded!'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'refund_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if ($request->refund_amount > $payment->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Refund amount cannot be greater than payment amount!'
            ]);
        }
        Stripe::setApiKey(config('services.stripe.secret'));
        try{
             $refund = Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => (int) ($request->refund_amount * 100),
            ]);
            // Save refund id and mark status pending until finalized
            $payment->stripe_refund_id = $refund->id ?? null;
           $payment->update([
            'payment_status' => 'refund_pending',
            'stripe_refund_id' => $refund->id,
            'notes' => $request->notes,
        ]);

        // Update order payment status if fully refunded
        if ($request->refund_amount == $payment->amount) {

            if ($order) {
                $order->payment_status = 'unpaid';
                $order->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment refunded successfully',
            'payment_status' => $payment->payment_status,
        ]);

        }catch(\Exception $e){
            return response()->json([
            'success' => false,
            'message' => 'Stripe refund failed: ' . $e->getMessage()
            ]);
        }


    }

    /**
     * Update payment notes
     */
    public function updateNotes(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $payment->notes = $request->notes;
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment notes updated successfully',
        ]);
    }

    /**
     * Get payment statistics
     */
    public function getStatistics()
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

        return response()->json($statistics);
    }

    /**
     * Get payment trends (last 12 months)
     */
    public function getTrends()
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

        return response()->json($trends);
    }

    /**
     * Get payment method breakdown
     */
    public function getMethodBreakdown()
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

        return response()->json($breakdown);
    }
}