<?php
namespace App\Services;

use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class DashboardService{
        public function __construct(protected DashboardRepository $repo)
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
        $analytics['total_orders'] = $this->repo->countAllOrders();
        $analytics['today_orders'] = $this->repo->countTodayOrders();
        $analytics['month_orders'] = $this->repo->countOrdersBetween($currentMonthStart, $currentMonthEnd);

        // Revenue Analytics
        $analytics['total_revenue'] = $this->repo->sumPaidRevenue();
        $analytics['today_revenue'] = $this->repo->sumPaidRevenueToday();
        $analytics['month_revenue'] = $this->repo->sumPaidRevenueBetween($currentMonthStart, $currentMonthEnd);
        $analytics['last_month_revenue'] = $this->repo->sumPaidRevenueBetween($lastMonthStart, $lastMonthEnd);

        // Calculate profit/loss (Revenue - Purchase Cost)
        $analytics['total_profit'] = $this->repo->sumProfitForPaidOrders();


        $analytics['month_profit'] = $this->repo->sumProfitForPaidOrders($currentMonthStart, $currentMonthEnd);

        // Products Statistics
        $analytics['total_products'] = $this->repo->countAllProducts();
        $analytics['low_stock_products'] = $this->repo->countLowStockProducts(10);
        $analytics['out_of_stock'] = $this->repo->countOutOfStockProducts();

        // Order Status Breakdown
        $analytics['pending_orders'] = $this->repo->countOrdersByStatus('pending');
        $analytics['processing_orders'] = $this->repo->countOrdersByStatus('processing');
        $analytics['shipped_orders'] = $this->repo->countOrdersByStatus('shipped');
        $analytics['delivered_orders'] = $this->repo->countOrdersByStatus('delivered');
        $analytics['cancelled_orders'] = $this->repo->countOrdersByStatus('cancelled');

        // Payment Status
        $analytics['pending_payments'] = $this->repo->countOrdersByPaymentStatus('pending');
        $analytics['paid_orders'] = $this->repo->countOrdersByPaymentStatus('paid');

        // Top Selling Products (Last 30 days)
        $analytics['top_products'] = $this->repo->getTopProductsLastDays(30, 5);

        // Recent Orders
        $analytics['recent_orders'] = $this->repo->getRecentOrders(10);

        // Sales Chart Data (Last 12 months)
        $analytics['monthly_sales'] = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $analytics['monthly_sales'][] = [
                'month' => $monthStart->format('M Y'),
                'sales' => $this->repo->sumPaidRevenueBetween($monthStart, $monthEnd),
                'orders' => $this->repo->countOrdersBetween($monthStart, $monthEnd)
            ];
        }

        // Profit/Loss Chart Data (Last 12 months)
        $analytics['monthly_profit'] = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $profit = $this->repo->sumProfitForPaidOrders($monthStart, $monthEnd);


            $analytics['monthly_profit'][] = [
                'month' => $monthStart->format('M Y'),
                'profit' => $profit
            ];
        }
    return $analytics;

    }
    public function customerDashboardService($userId){
         $stats = $this->repo->getCustomerStats((int) $userId);
        $totalSpent = (float) ($stats->total_spent ?? 0);
        $totalDiscount = (float) ($stats->total_discount ?? 0);
        $totalEarning = max(0, $totalSpent - $totalDiscount);


 return [
        'total_order_count'     => $stats->total_order_count ?? 0,
        'completed_order_count' => $stats->completed_order_count ?? 0,
        'pending_order_count'   => $stats->pending_order_count ?? 0,
        'cancelled_order_count' => $stats->cancelled_order_count ?? 0,
        'delivered_order_count' => $stats->delivered_order_count ?? 0,
        'shipped_order_count'   => $stats->shipped_order_count ?? 0,
        'returned_order_count'  => $stats->returned_order_count ?? 0,
        'total_spent'           => number_format($totalSpent, 2, '.', ''),
        'total_discount'        => number_format($totalDiscount, 2, '.', ''),
        'total_earning'         => number_format($totalEarning, 2, '.', ''),
        'recent_orders'         => $this->repo->getCustomerRecentOrders((int) $userId, 1),
        'wishlist_items'        => $this->getWishlistItems($userId),
        'cart_items'            => $this->getCartItems($userId),
    ];

    }

    private function getWishlistItems(int $userId)
    {
        $wishlist = $this->repo->getWishlistWithItems($userId);
        return $wishlist ? $wishlist->items->take(5)->map(fn($item)=> [
            'id'    => $item->product_id,
        'name'  => $item->product->name,
        'price' => $item->product->price,
        'image' => $item->product->image,
        'slug'  => $item->product->slug ?? '',
        ]) : collect([]);
    }
    private function getCartItems(int $userId){
        $cart = $this->repo->getCartWithItems($userId);
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
        $order = $this->repo->findCustomerOrderDetailsOrFail((string) $order_number, (int) Auth::id());

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
    return $this->repo->getCustomerOrderHistory((int) $userId, 10);
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
            'total_orders' => $this->repo->countOrdersByUser((int) $user->id),
            'total_spent' => $this->repo->sumPaidSpentByUser((int) $user->id),
            'address_count' => $user->addresses()->count(),
            'wishlist_count' => $this->repo->countWishlistItemsByUser((int) $user->id),
        ];
    }
}
