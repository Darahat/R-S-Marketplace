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
        $service_data = $this->dashboard_service->dashboard_service();
    //  dd($service_data['top_products']);
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
    public function customer_dashboard()
    {

        $dashboardData = $this->dashboard_service->customer_dashboard_service(Auth::id());

        return view('backend_panel_view_customer.pages.dashboard', [
            'page_title' => $this->page_title,
            'page_header' => 'Dashboard',
            'dashboard_data' => $dashboardData
        ]);
    }


public function customer_order_details($order_number){
  $detailsData =$this->dashboard_service->customer_order_details_service($order_number);

      return view('backend_panel_view_customer.pages.order_details', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $detailsData['order'],
        'progress_steps' => $detailsData['progressSteps']
    ]);
}

public function customer_order_history()
{
    $orders = $this->dashboard_service->customer_order_history_service(Auth::id());
    return view('backend_panel_view_customer.pages.order_list', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $orders,
     ]);
}
    public function customer_profile_setting()
    {

        $profile = $this->dashboard_service->customer_profile_setting_service(Auth::user());


        return view('backend_panel_view_customer.pages.profile_setting', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Settings',
            'profile' => $profile,

        ]);

    }

    public function customer_profile()
    {
        $profileData = $this->dashboard_service->customer_profile_service(Auth::user());

        return view('backend_panel_view_customer.pages.profile', [
            'page_title' => $this->user_page_title,
            'page_header' => 'My Profile',
            'profile' => $profileData,
        ]);
    }


}
