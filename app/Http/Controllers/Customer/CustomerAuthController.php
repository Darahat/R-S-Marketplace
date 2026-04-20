<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CustomerPasswordRecoveryService;
use App\Services\RoleRedirectService;
use App\Http\Requests\RegistrarRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordLinkRequest;
use App\Http\Requests\ResetCustomerPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
class CustomerAuthController extends Controller
{
    public function __construct(
        private AuthService $service,
        private RoleRedirectService $roleRedirectService,
        private CustomerPasswordRecoveryService $customerPasswordRecoveryService,
    ){}


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
            'redirect' => $this->roleRedirectService->redirectByRole($user),
        ]);
    }
        return redirect()->intended($this->roleRedirectService->redirectByRole($user));

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

    public function showForgotPassword()
    {
        return view('frontend_view.pages.auth.forgot_password');
    }

    public function sendPasswordResetLink(ForgotPasswordLinkRequest $request)
    {
        $status = $this->customerPasswordRecoveryService->sendResetLink($request->validated('email'));

        return back()->with('status', __($status));
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('frontend_view.pages.auth.reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetCustomerPasswordRequest $request)
    {
        $status = $this->customerPasswordRecoveryService->resetPassword($request->validated());

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('home', ['auth' => 'login'])->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
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
