<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Order;
use Hash;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    protected $sms_api;
    protected $db_controller;
    protected $page_title;
    protected $user_page_title;

	public function __construct(){

        $this->page_title = "Admin Panel";
        $this->user_page_title = "Customer Panel";

    }

    public function dashboard()
    {
        //   enableuser=1 and expiration>'$date'" radacct where acctstoptime is null
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();



        // print_r($nas_status);
        // exit();

        return view('backend_panel_view.pages.dashboard', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Dashboard',

        ]);

    }
    public function customer_dashboard()
    {
        $user = Auth::user();
        $dashboardData['total_order_count'] = DB::table('orders')->where('user_id', $user->id)->count();
        $dashboardData['completed_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'completed')->count();
        $dashboardData['pending_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'pending')->count();
        $dashboardData['cancelled_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'cancelled')->count();
        $dashboardData['delivered_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'delivered')->count();
        $dashboardData['shipped_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'shipped')->count();
        $dashboardData['returned_order_count'] = DB::table('orders')->where('user_id', $user->id)->where('order_status', 'returned')->count();
        $dashboardData['total_spent'] =$total_spent = DB::table('orders')->where('user_id', $user->id)->sum('total_amount');
        $dashboardData['total_discount'] = $total_discount= DB::table('orders')->where('user_id', $user->id)->sum('discount');
        $dashboardData['total_earning'] = $total_earning= $total_spent - $total_discount;
        $dashboardData['total_earning'] = $total_earning < 0 ? 0 : $total_earning;
        $dashboardData['total_earning'] = number_format($total_earning, 2, '.', '');
        $dashboardData['total_spent'] = number_format($total_spent, 2, '.', '');
        $dashboardData['total_discount'] = number_format($total_discount, 2, '.', '');
        $dashboardData['total_earning'] = $total_spent - $total_discount;
        $dashboardData['total_earning'] = $total_earning < 0 ? 0 : $total_earning;
        $dashboardData['recent_orders'] = DB::table('orders')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(1))
            ->get();

        // Get wishlist items
        $wishlist = Wishlist::where('user_id', $user->id)->with('items.product')->first();
        $dashboardData['wishlist_items'] = $wishlist ? $wishlist->items->take(5)->map(function($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'image' => $item->product->image,
                'slug' => $item->product->slug ?? '',
            ];
        }) : collect([]);

        // Get cart items
        $cart = Cart::where('user_id', $user->id)->with('items.product')->first();
        $dashboardData['cart_items'] = $cart ? $cart->items->take(5)->map(function($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'image' => $item->product->image,
                'slug' => $item->product->slug ?? '',
            ];
        }) : collect([]);

        return view('backend_panel_view_customer.pages.dashboard', [
            'page_title' => $this->page_title,
            'page_header' => 'Dashboard',
            'dashboard_data' => $dashboardData
        ]);
    }


public function customer_order_details($id){
    $order = DB::table('orders')->where('id', $id)->first();

    // Define status path
    $statusPath = [
        'packaged',
        'shipped',
        'delivered',
    ];

    // Mark which steps are completed
    $currentStatusIndex = array_search($order->order_status, $statusPath);

    // Prepare path with status flags
    $progressSteps = [];
    foreach ($statusPath as $index => $status) {
        $progressSteps[] = [
            'label' => ucfirst($status),
            'completed' => $index <= $currentStatusIndex,
            'is_current' => $index === $currentStatusIndex,
        ];
    }

      return view('backend_panel_view_customer.pages.order_details', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $order,
        'progress_steps' => $progressSteps
    ]);
}

public function customer_order_history()
{
    $user = Auth::user();
    $orders = Order::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    $orders->setPath('customer-order-history');
    $orders->appends(request()->query());
    $orders->links();
    return view('backend_panel_view_customer.pages.order_list', [
        'page_title' => $this->page_title,
        'page_header' => 'Dashboard',
        'orderData' => $orders,
     ]);
}
    public function customer_profile_setting()
    {

       $user = Auth::user();

        $profile = [
            'last_login' => $user && $user->last_login
                ? Carbon::parse($user->last_login)->diffForHumans()
                : 'Never',
        ];

        return view('backend_panel_view_customer.pages.profile_setting', [
            'page_title' =>  $this->page_title,
            'page_header' => 'Settings',
            'profile' => $profile,

        ]);

    }


}