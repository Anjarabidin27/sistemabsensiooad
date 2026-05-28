<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Show student login form. */
    public function showStudentLogin()
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.home');
        }
        return view('auth.student_login');
    }

    /** Handle student login request. */
    public function studentLogin(Request $request)
    {
        $credentials = $request->validate([
            'student_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('student')->attempt([
            'student_number' => $credentials['student_number'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('student.home');
        }

        throw ValidationException::withMessages([
            'student_number' => 'NIM atau password salah, atau akun Anda dinonaktifkan.',
        ]);
    }

    /** Handle student logout. */
    public function studentLogout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }

    /** Show admin login form. */
    public function showAdminLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin_login');
    }

    /** Handle admin login request. */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password administrator salah.',
        ]);
    }

    /** Handle admin logout. */
    public function adminLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
