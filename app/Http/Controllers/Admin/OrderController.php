<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderNotesRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $page_title;

    public function __construct(protected OrderService $order_service)
    {
        $this->page_title = "Order Management";
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'payment_status' =>$request->input('payment_status'),
            'payment_method' =>$request->input('payment_method'),
            'date_from' =>$request->input('date_from'),
            'date_to' =>$request->input('date_to'),
        ];
        $orders = $this->order_service->getOrdersService($filters);


        return view('backend_panel_view_admin.pages.orders.index', [
            'orders' => $orders,
            'page_title' => $this->page_title,
            'page_header' => 'Orders',
            'statuses' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'],
            'paymentStatuses' => ['unpaid', 'paid', 'failed'],
            'paymentMethods' => ['cod', 'card', 'bkash', 'stripe'],
        ]);
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = $this->order_service->findDetailedByIdOrFail((int) $id);
        return view('backend_panel_view_admin.pages.orders.show', [
            'order' => $order,
            'page_title' => $this->page_title,
            'page_header' => 'Order Details',
            'statuses' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'],
            'paymentStatuses' => ['unpaid', 'paid', 'failed'],
            'paymentMethods' => ['cod', 'card', 'bkash', 'stripe'],
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        $validated = $request->validated();
        $updated_order = $this->order_service->updateStatusService($validated, $id);

        return response()->json([
            'success' => true,
            'message' => "Order status updated from '{$updated_order['oldStatus']}' to '{$updated_order['order_status']}'",
            'status' => $updated_order['order_status'],
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, $id)
    {
        $validator = $request->validated();
        $order = $this->order_service->updatePaymentStatusService($validator,$id);
        return response()->json([
            'success' => true,
            'message' => "Payment status updated from '{$order['oldStatus']}' to '{$order['payment_status']}'",
            'payment_status' => $order['payment_status'],
        ]);
    }

    /**
     * Update order notes
     */
    public function updateNotes(UpdateOrderNotesRequest $request, $id)
    {
    $validator =$request->validated();
    $this->order_service->updateNotesService($validator,$id);

        return response()->json([
            'success' => true,
            'message' => 'Order notes updated successfully',
        ]);
    }

    /**
     * Print order invoice
     */
    public function printInvoice($id)
    {
        $order = $this->order_service->findDetailedByIdOrFail((int) $id);

        return view('backend_panel_view_admin.pages.orders.invoice', [
            'order' => $order,
        ]);
    }

    /**
     * Get order statistics
     */
    public function getStatistics()
    {
        $statistics = $this->order_service->getStatisticsService();

        return response()->json($statistics);
    }
}
