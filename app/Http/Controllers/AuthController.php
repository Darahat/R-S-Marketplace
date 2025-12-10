<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    // Show Login Form
    public function showLogin()
    {
        return view('frontend_view.pages.auth.login');
    }
    public function adminLogin(Request $request)
    {
        // Show admin login form on GET
        if ($request->isMethod('get')) {
            return view('backend_panel_view.pages.auth.admin_login');
        }

        // Handle admin login on POST
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            /** @var User|null $user */
            $user = Auth::user();

            // Check if user is admin
            if (!$user || $user->user_type !== 'ADMIN') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ]);
            }

            // Update login details
            $user->last_login = now();
            $user->last_ip = $request->ip();
            $user->last_location = $request->getClientIp();
            $user->last_device = $request->header('User-Agent');
            $user->save();

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle Customer Login
    public function login(Request $request)
    {
        // return $request->all();
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                $user->last_login    = now();
                $user->last_ip       = $request->ip();
                $user->last_location = $request->getClientIp();
                $user->last_device   = $request->header('User-Agent');
                $user->save();

                // Sync guest cart to user cart
                $this->syncGuestCart();

                // Sync guest wishlist to user wishlist
                $this->syncGuestWishlist();
            }

            if(Auth::user()->user_type == 'ADMIN'){
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('home'));
            }

        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Show Registration Form
    public function showRegister()
    {
        return view('frontend_view.pages.auth.register');
    }

    // Handle Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'user_type' => 'CUSTOMER',
        ]);

        Auth::login($user);

        // Sync guest cart and wishlist after registration
        $this->syncGuestCart();
        $this->syncGuestWishlist();

        return redirect()->route('home')->with('success', 'Registration successful!');
    }

    // Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Sync guest cart to database when user logs in or registers
     */
    protected function syncGuestCart()
    {
        if (session()->has('cart')) {
            $guestCart = session('cart', []);
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            foreach ($guestCart as $productId => $item) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += $item['quantity'];
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }
            }

            session()->forget('cart');
        }
    }

    /**
     * Sync guest wishlist to database when user logs in or registers
     */
    protected function syncGuestWishlist()
    {
        if (session()->has('wishlist')) {
            $guestWishlist = session('wishlist', []);
            $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);

            foreach ($guestWishlist as $productId) {
                WishlistItem::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId
                ]);
            }

            session()->forget('wishlist');
        }
    }
}
