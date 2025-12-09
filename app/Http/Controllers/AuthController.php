<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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
            return view('frontend_view.pages.auth.admin_login');
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
}
