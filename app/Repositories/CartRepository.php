<?php
namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
class CartRepository{
    public function getCartItem(int $cartId, int $productId):?CartItem{
         return CartItem::where('cart_id', $cartId)
                ->where('product_id', (int) $productId)
                ->first();
    }
    public function getUserIdFromCart(){
        return Cart::where('user_id', Auth::id())->first();
    }

}
