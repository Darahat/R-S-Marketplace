<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    public function __construct(  private AuthService $service){}

    // Show Admin Login Form (GET)
    public function showAdminLogin()
    {
        Log::info('Show Admin Login Form called');
        return view('backend_panel_view_admin.pages.auth.admin_login');
    }

    // Handle Admin Login (POST)
    public function adminLogin(LoginRequest $request)
    {
        Log::info('AdminLogin POST called', [
            'email' => $request->email ?? 'no email'
        ]);

        // Handle admin login on POST
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->remember)) {
            /** @var User|null $user */
            $user = Auth::user();

            // Check if user is admin
            if (!$user || !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ])->onlyInput('email');
            }

            // Update login details
            $this->service->recordLoginMetaData($user,$request->ip(),$request->header('User-Agent'));

            session()->regenerate();
            return redirect()->intended($this->service->redirectByRole($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }


    // Handle Logout
    public function logout()
    {
        $this->service->logout();
        return redirect('/');
    }


}
