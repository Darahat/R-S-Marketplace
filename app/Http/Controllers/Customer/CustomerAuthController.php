<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use App\Http\Requests\RegistrarRequest;
use App\Http\Requests\LoginRequest;
class CustomerAuthController extends Controller
{
    public function __construct(  private AuthService $service){}


    // Handle Customer Login
    public function login(LoginRequest $request)
    {
        // return $request->all();
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            /** @var User|null $user */
            $user = Auth::user();
            if ($user) {
                $this->service->recordLoginMetaData($user,$request->ip(),$request->header('User-Agent'));
                $this->service->syncUserData($user);
            }

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' =>  $this->service->redirectByRole($user),
                ]);
            }

           return $this->service->redirectByRole($user);

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
    public function register(RegistrarRequest $request)
    {
        $validator = $request->validated();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'user_type' => 'CUSTOMER',
        ]);
        Auth::login($user);
        $user = Auth::user();
        if ($user) {
                $this->service->recordLoginMetaData($user,$request->ip(),$request->header('User-Agent'));
                $this->service->syncUserData($user);
            }
        // Sync guest cart and wishlist after registration
        // Return JSON for AJAX requests

        return redirect()->route('home')->with('success', 'Registration successful!');
    }
    // Handle Logout
    public function logout()
    {
        $this->service->logout();
        return redirect('/');
    }
}
