<?php
namespace App\Services;

use App\Repositories\UserAddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Order;
use App\Jobs\SendOrderStatusNotificationJob;

use Illuminate\Support\Facades\Storage;
class OrderService{
      use AuthorizesRequests;
       protected $siteTitle;
    public function __construct(protected ProductService $product_service)
    {
        $this->siteTitle = '';
    }
    public function getOrdersService(array $filters){
// Search by order number or customer name
return Order::with(['user','address','items.product'])->
when($filters['search'] ?? null, function ($q, $search){
    $q->where(function($q) use ($search){
        $q->where('order_number', 'like', "%$search%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                  });
    });
})
->when($filters['status'] ?? null, fn($q, $v)=> $q->where('order_status', $v))
->when($filters['payment_status'] ?? null, fn($q, $v)=> $q->where('payment_status', $v))
->when($filters['payment_method'] ?? null, fn($q, $v)=> $q->where('payment_method', $v))
->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
->latest()
        ->paginate(20);

    }

    public function updateStatusService($validator,$id):array{
        $order = Order::findOrFail($id);
        $oldStatus = $order->order_status;
        $order->order_status = $validator['status'];

        // Set timestamps for certain statuses
        if ($order->order_status == 'shipped' && !$order->shipped_at) {
            $order->shipped_at = now();
        }

        if ($order->order_status == 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();
        SendOrderStatusNotificationJob::dispatch($order, $oldStatus, $order->order_status)->onQueue('default');
        return [
            'order_status' =>$order->order_status,
            'oldStatus' => $oldStatus,
         ];
    }
    public function updatePaymentStatusService($validator,$id):array{
        $order = Order::findOrFail($id);
        $oldStatus = $order->payment_status;
        $order->payment_status = $validator['payment_status'];
         $order->save();
         return [
            'payment_status' =>$order->payment_status,
            'oldStatus' => $oldStatus,
         ];
    }
    public function updateNotesService($validator,$id){


        $order = Order::findOrFail($id);
        $order->notes = $validator['notes'] ?? null;
        return $order->save();

}
}
