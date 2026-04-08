<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Stripe\Refund;
use App\Http\Requests\UpdateOrderNotesRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Http\Requests\RefundRequest;
use App\Services\AdminPartPaymentService;
class PaymentController extends Controller
{
    protected $page_title;

    public function __construct(protected AdminPartPaymentService $admin_part_payment_service)
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

        return view('backend_panel_view_admin.pages.payments.index', [
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

        return view('backend_panel_view_admin.pages.payments.show', [
            'payment' => $payment,
            'page_title' => $this->page_title,
            'page_header' => 'Payment Details',
            'paymentStatuses' => ['pending', 'processing', 'completed', 'failed', 'refund_pending', 'refunded'],
        ]);
    }

    /**
     * Process a payment
     */
    public function process($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status == 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is already completed!'
            ]);
        }
        $payment_status = $this->admin_part_payment_service->processService($id, $payment);


        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_status' => $payment_status,
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markFailed(UpdateOrderNotesRequest $request, $id)
    {
        $validator = $request->validator();
        $payment_status = $this->admin_part_payment_service->markFailedService($validator, $id);
        return response()->json([
            'success' => true,
            'message' => 'Payment marked as failed',
            'payment_status' => $payment_status,
        ]);
    }

    /**
     * Refund a payment
     */
    public function refund(RefundRequest $request, $id)
    {

    //  $validator = $request->validated();
//         $payment_status =$this->admin_part_payment_service->refundService($validator, $id);
            $payment = Payment::findOrFail($id);
            $validator = $request->validated();
            $payment_refund =$this->admin_part_payment_service->refundService($validator, $payment);
            return response()->json([
                'success' => false,
                'message' => 'Payment is already completed!'
            ]);

    }

    /**
     * Update payment notes
     */
    public function updateNotes(UpdateOrderNotesRequest $request, $id)
    {
        $validator = $request->validated();
        return response()->json($this->admin_part_payment_service->updateNotesService($validator, $id));
    }

    public function getStatistics(){
    return response()->json($this->admin_part_payment_service->getStatisticsService());
    }
    public function getTrends(){
        return response()->json($this->admin_part_payment_service->getTrendsService());
    }
    public function getMethodBreakdown(){
        return response()->json($this->admin_part_payment_service->getMethodBreakdownService());
    }



}
