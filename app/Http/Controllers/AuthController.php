<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
{
    Auth::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password)
    ]);

    // 🔥 redirect to Next.js login
    return redirect('http://localhost:3000/login');
}

// LOGIN
public function login(Request $request)
{
    $user = Auth::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return redirect('http://localhost:3000/login');
    }

    Session::put('auth_user', $user);

    // 🔥 redirect to Next.js dashboard
    return redirect('http://localhost:3000/dashboard');
}

    // DASHBOARD
    public function dashboard()
    {
        if (!Session::has('auth_user')) {
            return redirect('/login');
        }

        return view('dashboard');
    }

    // LOGOUT
   public function logout()
{
    Session::forget('auth_user');

    // 🔥 redirect to Next.js login page
    return redirect('http://localhost:3000/login');
}

}
