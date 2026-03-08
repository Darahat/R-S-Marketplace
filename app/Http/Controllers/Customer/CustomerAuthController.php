<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
        $user = $this->service->attemptLogin(
        $request->validated(),
        $request->boolean('remember'),
        $request->ip(),
        $request->userAgent()
    );


     if (!$user) {
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

        if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'redirect' => $this->service->redirectByRole($user),
        ]);
    }
        return redirect()->intended($this->service->redirectByRole($user));

        }



    // Show Registration Form
    public function showRegister()
    {
        return view('frontend_view.pages.auth.register');
    }

    // Handle Registration
    public function register(RegistrarRequest $request)
    {
        $user = $this->service->register(
        $request->validated(),
        $request->ip(),
        $request->userAgent()
    );


        // Sync guest cart and wishlist after registration
        // Return JSON for AJAX requests

        return redirect()->route('home')->with('success', 'Registration successful!');
    }
    // Handle Logout
    public function logout()
    {
        $this->service->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    }
}
