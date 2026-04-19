<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\AdminAuthService;
use App\Services\AuthService;
use App\Services\RoleRedirectService;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private AdminAuthService $adminAuthService,
        private RoleRedirectService $roleRedirectService,
    ){}

    // Show Admin Login Form (GET)
    public function showAdminLogin()
    {
        return view('backend_panel_view_admin.pages.auth.admin_login');
    }

    // Handle Admin Login (POST)
    public function adminLogin(LoginRequest $request)
    {
        // Attempt admin login via service
        $user = $this->adminAuthService->attemptLogin(
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

        return redirect()->intended($this->roleRedirectService->redirectByRole($user));
    }


    // Handle Logout
    public function logout()
    {
        $this->authService->logout();
        return redirect('/');
    }

    public function loginAudits(Request $request)
    {
        $audits = $this->adminAuthService->getLoginAudits($request->all(), 20);

        return view('backend_panel_view_admin.pages.auth.login_audits', [
            'page_title' => 'Login Audits',
            'page_header' => 'Authentication Audit Trail',
            'audits' => $audits,
        ]);
    }


}
