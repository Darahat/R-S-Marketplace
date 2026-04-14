<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        // Attempt admin login via service
        $user = $this->service->attemptAdminLogin(
            $request->validated(),
            $request->boolean('remember'),
            $request->ip(),
            $request->header('User-Agent')
        );

        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records, or you do not have admin access.',
            ])->onlyInput('email');
        }

        return redirect()->intended($this->service->redirectByRole($user));
    }


    // Handle Logout
    public function logout()
    {
        $this->service->logout();
        return redirect('/');
    }

    public function loginAudits(Request $request)
    {
        $audits = $this->service->getLoginAudits($request->all(), 20);

        return view('backend_panel_view_admin.pages.auth.login_audits', [
            'page_title' => 'Login Audits',
            'page_header' => 'Authentication Audit Trail',
            'audits' => $audits,
        ]);
    }


}
