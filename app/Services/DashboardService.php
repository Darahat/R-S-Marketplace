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
class DashboardService{
      use AuthorizesRequests;
    public function __construct()
    {
    }
    public function dashboardService(){
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
        $analytics['total_profit'] = OrderItem::whereHas('order', fn($q)=> $q->where('payment_status','paid'))->with('product')->get()
        ->sum(fn($item)=>($item->price - $item->product->purchase_price)* $item->quantity);


        $analytics['month_profit'] = OrderItem::whereHas('order', fn($q)=> $q->where('payment_status','paid'))->whereBetween('created_at', [$currentMonthStart,$currentMonthEnd])->with('product')->get()
        ->sum(fn($item)=>($item->price - $item->product->purchase_price)* $item->quantity);

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
        $analytics['top_products'] = OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', Carbon::now()->subDays(30)))
        ->with('product:id,name,image,price')->get()->groupBy('product_id')
        ->map(fn($items)=>[
            'product' => $items->first()->product,
            'total_sold' => $items->sum('quantity'),
            'total_revenue' => $items->sum(fn($item) => $item->price * $item->quantity)
        ])->sortByDesc('total_sold')
        ->take(5)
        ->values();

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
            $profit = OrderItem::whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->with('product')
                ->get()
                ->sum(fn($item) => ($item->price - $item->product->purchase_price) * $item->quantity);


            $analytics['monthly_profit'][] = [
                'month' => $monthStart->format('M Y'),
                'profit' => $profit
            ];
        }
    return $analytics;

    }
    public function customerDashboardService($userId){
         $stats = Order::where('user_id', $userId)
        ->selectRaw("COUNT(*) as total_order_count,
            SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed_order_count,
            SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_order_count,
            SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_order_count,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_order_count,
            SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_order_count,
            SUM(CASE WHEN order_status = 'returned' THEN 1 ELSE 0 END) as returned_order_count,
            COALESCE(SUM(total_amount), 0) as total_spent,
            COALESCE(SUM(discount), 0) as total_discount")->first();
        $totalSpent = (float) $stats->total_spent;
        $totalDiscount = (float) $stats->total_discount;
        $totalEarning = max(0, $totalSpent - $totalDiscount);


 return [
        'total_order_count'     => $stats->total_order_count,
        'completed_order_count' => $stats->completed_order_count,
        'pending_order_count'   => $stats->pending_order_count,
        'cancelled_order_count' => $stats->cancelled_order_count,
        'delivered_order_count' => $stats->delivered_order_count,
        'shipped_order_count'   => $stats->shipped_order_count,
        'returned_order_count'  => $stats->returned_order_count,
        'total_spent'           => number_format($totalSpent, 2, '.', ''),
        'total_discount'        => number_format($totalDiscount, 2, '.', ''),
        'total_earning'         => number_format($totalEarning, 2, '.', ''),
        'recent_orders'         => Order::where('user_id', $userId)
                                    ->where('created_at', '>=', now()->subDays(1))
                                    ->get(),
        'wishlist_items'        => $this->getWishlistItems($userId),
        'cart_items'            => $this->getCartItems($userId),
    ];

    }

    private function getWishlistItems(int $userId)
    {
        $wishlist = Wishlist::where('user_id',$userId)->with('items.product')->first();
        return $wishlist ? $wishlist->items->take(5)->map(fn($item)=> [
            'id'    => $item->product_id,
        'name'  => $item->product->name,
        'price' => $item->product->price,
        'image' => $item->product->image,
        'slug'  => $item->product->slug ?? '',
        ]) : collect([]);
    }
    private function getCartItems(int $userId){
        $cart = Cart::where('user_id', $userId)->with('items.product')->first();
        return $cart ? $cart->items->take(5)->map(fn($item)=>[
'id'       => $item->product_id,
        'name'     => $item->product->name,
        'price'    => $item->price,
        'quantity' => $item->quantity,
        'image'    => $item->product->image,
        'slug'     => $item->product->slug ?? '',
    ]) : collect([]);

    }

    public function customerOrderDetailsService($order_number){
          $order = Order::where('order_number', $order_number)
        ->where('user_id', Auth::id())
        ->with(['address.district', 'address.upazila', 'address.union', 'items.product', 'payments'])
        ->firstOrFail();

    // Define status path
    $statusPath = $order->order_status === 'to_pay'
        ? ['to_pay', 'Processing', 'packaged', 'shipped', 'delivered']
        : ['Processing', 'packaged', 'shipped', 'delivered'];
      // Mark which steps are completed
    $currentStatusIndex = array_search($order->order_status, $statusPath);

    // Prepare path with status flags
    $progressSteps = [];
    foreach ($statusPath as $index => $status) {
        $progressSteps[] = [
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'completed' => $currentStatusIndex !== false && $index < $currentStatusIndex,
            'is_current' => $index === $currentStatusIndex,
        ];
    }

    return [
        'progressSteps' => $progressSteps,
        'order' => $order,
    ];
    }

    public function customerOrderHistoryService($userId){
    $orders = Order::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    $orders->setPath('customer-order-history');
    $orders->appends(request()->query());
    $orders->links();
    return $orders;
    }
    public function customerProfileSettingService($user){
         $profile = [
            'last_login' => $user && $user->last_login
                ? Carbon::parse($user->last_login)->diffForHumans()
                : 'Never',
        ];
        return $profile;
    }

    public function customerProfileService($user){
        return [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'profile_photo' => $user->profile_photo,
            'member_since' => $user->created_at->format('F d, Y'),
            'last_login' => $user->last_login
                ? Carbon::parse($user->last_login)->diffForHumans()
                : 'Never',
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'address_count' => $user->addresses()->count(),
            'wishlist_count' => Wishlist::where('user_id', $user->id)
                ->withCount('items')->first()?->items_count ?? 0,
        ];
    }
}
