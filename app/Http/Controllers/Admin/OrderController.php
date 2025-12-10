<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $page_title;

    public function __construct()
    {
        $this->page_title = "Order Management";
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'address', 'items.product']);

        // Search by order number or customer name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->order_status != '') {
            $query->where('status', $request->order_status);
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

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend_panel_view.pages.orders.index', [
            'orders' => $orders,
            'page_title' => $this->page_title,
            'page_header' => 'Orders',
            'statuses' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'],
            'paymentStatuses' => ['unpaid', 'paid', 'failed'],
            'paymentMethods' => ['cod', 'card', 'bkash', 'nagad', 'rocket'],
        ]);
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::with(['user', 'address', 'items.product'])->findOrFail($id);

        return view('backend_panel_view.pages.orders.show', [
            'order' => $order,
            'page_title' => $this->page_title,
            'page_header' => 'Order Details',
            'statuses' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'],
            'paymentStatuses' => ['unpaid', 'paid', 'failed'],
            'paymentMethods' => ['cod', 'card', 'bkash', 'nagad', 'rocket'],
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $oldStatus = $order->order_status;
        $order->order_status = $request->status;

        // Set timestamps for certain statuses
        if ($request->status == 'shipped' && !$order->shipped_at) {
            $order->shipped_at = now();
        }

        if ($request->status == 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Order status updated from '{$oldStatus}' to '{$request->status}'",
            'status' => $order->order_status,
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:unpaid,paid,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $oldStatus = $order->payment_status;
        $order->payment_status = $request->payment_status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Payment status updated from '{$oldStatus}' to '{$request->payment_status}'",
            'payment_status' => $order->payment_status,
        ]);
    }

    /**
     * Update order notes
     */
    public function updateNotes(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $order->notes = $request->notes;
        $order->save();

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
        $order = Order::with(['user', 'address', 'items.product'])->findOrFail($id);

        return view('backend_panel_view.pages.orders.invoice', [
            'order' => $order,
        ]);
    }

    /**
     * Get order statistics
     */
    public function getStatistics()
    {
        $statistics = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_payment' => Order::where('payment_status', 'unpaid')->sum('total'),
        ];

        return response()->json($statistics);
    }
}