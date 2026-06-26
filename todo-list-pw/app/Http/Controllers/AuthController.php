<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login menggunakan Form Request validation
     */
    public function login(LoginRequest $request)
    {
        // Data sudah tervalidasi oleh LoginRequest
        $credentials = $request->only('username', 'password');

        // Cek user berdasarkan username
        $user = User::where('name', $credentials['username'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'username' => 'User dengan username ' . $credentials['username'] . ' tidak ditemukan',
            ])->onlyInput('username');
        }

        // Coba login dengan username dan password
        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']], $request->has('remember'))) {
            // Login berhasil, regenerate session
            $request->session()->regenerate();
            return redirect()->intended('/todo')->with('success', 'Login berhasil!');
        }

        // Login gagal, kembali ke login dengan error
        return back()->withErrors([
            'username' => 'Username atau password salah',
        ])->onlyInput('username');
    }

    /**
     * Logout dan invalidate session
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}
