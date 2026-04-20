<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateOrderNotesRequest;
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
        $filters = $request->only([
            'search',
            'payment_status',
            'payment_method',
            'date_from',
            'date_to',
        ]);
        $payments = $this->admin_part_payment_service->getPaymentsService($filters);

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
        $payment = $this->admin_part_payment_service->getPaymentDetailsService((int) $id);

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
        $payment = $this->admin_part_payment_service->getPaymentDetailsService((int) $id);
        if ($payment->payment_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is already completed!'
            ]);
        }
        $payment_status = $this->admin_part_payment_service->processService((int) $id);


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
        $validator = $request->validated();
        $payment_status = $this->admin_part_payment_service->markFailedService($validator, (int) $id);
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
        $validator = $request->validated();
        return response()->json(
            $this->admin_part_payment_service->refundService($validator, (int) $id)
        );
    }

    /**
     * Update payment notes
     */
    public function updateNotes(UpdateOrderNotesRequest $request, $id)
    {
        $validator = $request->validated();
        return response()->json($this->admin_part_payment_service->updateNotesService($validator, (int) $id));
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
