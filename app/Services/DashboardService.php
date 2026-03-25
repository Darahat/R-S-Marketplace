<?php
namespace App\Services;

use App\Repositories\UserAddressRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use App\Models\Address;
class AddressService{
      use AuthorizesRequests;
    public function __construct(private UserAddressRepository $repo)
    {
    }
    public function dashboard(){
       $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Overview Statistics
        $analytics = [];

        // Total Orders
        $analytics['total_orders'] = Order::count();
        $analytics['today_orders'] = Order::whereDate('created_at', Carbon::today())->count();
        $analytics['month_orders'] = Order::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();

        // Revenue Analytics
        $analytics['total_revenue'] = Order::where('payment_status', 'paid')->sum('total_amount');
        $analytics['today_revenue'] = Order::where('payment_status', 'paid')->whereDate('created_at', Carbon::today())->sum('total_amount');
        $analytics['month_revenue'] = Order::where('payment_status', 'paid')->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->sum('total_amount');
        $analytics['last_month_revenue'] = Order::where('payment_status', 'paid')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');

        // Calculate profit/loss (Revenue - Purchase Cost)
        $analytics['total_profit'] = OrderItem::
            join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->select(DB::raw('SUM((order_items.price - products.purchase_price) * order_items.quantity) as profit'))
            ->value('profit') ?? 0;

        $analytics['month_profit'] = OrderItem::
            join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$currentMonthStart, $currentMonthEnd])
            ->select(DB::raw('SUM((order_items.price - products.purchase_price) * order_items.quantity) as profit'))
            ->value('profit') ?? 0;

        // Products Statistics
        $analytics['total_products'] = Product::count();
        $analytics['low_stock_products'] = Product::where('stock', '<', 10)->count();
        $analytics['out_of_stock'] = Product::where('stock', '=', 0)->count();

        // Order Status Breakdown
        $analytics['pending_orders'] = Order::where('order_status', 'pending')->count();
        $analytics['processing_orders'] = Order::where('order_status', 'processing')->count();
        $analytics['shipped_orders'] = Order::where('order_status', 'shipped')->count();
        $analytics['delivered_orders'] = Order::where('order_status', 'delivered')->count();
        $analytics['cancelled_orders'] = Order::where('order_status', 'cancelled')->count();

        // Payment Status
        $analytics['pending_payments'] = Order::where('payment_status', 'pending')->count();
        $analytics['paid_orders'] = Order::where('payment_status', 'paid')->count();

        // Top Selling Products (Last 30 days)
        $analytics['top_products'] = OrderItem::
            join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->select('products.id', 'products.name', 'products.image', 'products.price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.image', 'products.price')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Recent Orders
        $analytics['recent_orders'] = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Sales Chart Data (Last 12 months)
        $analytics['monthly_sales'] = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $analytics['monthly_sales'][] = [
                'month' => $monthStart->format('M Y'),
                'sales' => Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('total_amount'),
                'orders' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count()
            ];
        }

        // Profit/Loss Chart Data (Last 12 months)
        $analytics['monthly_profit'] = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $profit = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.payment_status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->select(DB::raw('SUM((order_items.price - products.purchase_price) * order_items.quantity) as profit'))
                ->value('profit') ?? 0;

            $analytics['monthly_profit'][] = [
                'month' => $monthStart->format('M Y'),
                'profit' => $profit
            ];
        }


    }
    }
