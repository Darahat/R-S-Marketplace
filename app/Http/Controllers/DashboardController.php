<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $sms_api;
    protected $db_controller;
    protected $page_title;
    protected $user_page_title;

	public function __construct(protected DashboardService $dashboard_service){

        $this->page_title = "Admin Panel";
        $this->user_page_title = "Customer Panel";

    }

    public function dashboard()
    {
        $user = Auth::user();
        $service_data = $this->dashboard_service->dashboardService();
        if($user->user_type == 'ADMIN'){
                return view('backend_panel_view_admin.pages.dashboard', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Dashboard',
            'analytics' => $service_data,
        ]);
            }
            elseif($user->user_type == 'CUSTOMER'){
                   return view('backend_panel_view_customer.pages.dashboard', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Dashboard',
         ]);
            }
    }
    public function customerDashboard()
    {

        $dashboardData = $this->dashboard_service->customerDashboardService(Auth::id());

        return view('backend_panel_view_customer.pages.dashboard', [
            'page_title' => $this->page_title,
            'page_header' => 'Dashboard',
            'dashboard_data' => $dashboardData
        ]);
    }


public function customerOrderDetails($order_number){
  $detailsData =$this->dashboard_service->customerOrderDetailsService($order_number);

      return view('backend_panel_view_customer.pages.order_details', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $detailsData['order'],
        'progress_steps' => $detailsData['progressSteps']
    ]);
}

public function customerOrderHistory()
{
    $orders = $this->dashboard_service->customerOrderHistoryService(Auth::id());
    return view('backend_panel_view_customer.pages.order_list', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $orders,
     ]);
}
    public function customerProfileSetting()
    {

        $profile = $this->dashboard_service->customerProfileSettingService(Auth::user());


        return view('backend_panel_view_customer.pages.profile_setting', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Settings',
            'profile' => $profile,

        ]);

    }

    public function customerProfile()
    {
        $profileData = $this->dashboard_service->customerProfileService(Auth::user());

        return view('backend_panel_view_customer.pages.profile', [
            'page_title' => $this->user_page_title,
            'page_header' => 'My Profile',
            'profile' => $profileData,
        ]);
    }

    public function cancelOrder($order_id)
    {
        try {
            $order = Order::findOrFail($order_id);

            // Verify the order belongs to the authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Only allow cancellation of pending or processing orders
            if (!in_array(strtolower($order->order_status), ['pending', 'processing'])) {
                return response()->json(['message' => 'This order cannot be cancelled'], 422);
            }

            // Update order status to cancelled
            $order->update(['order_status' => 'cancelled']);

            return response()->json(['message' => 'Order cancelled successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Order cancellation error: ' . $e->getMessage());
            return response()->json(['message' => 'Error cancelling order'], 500);
        }
    }
}
