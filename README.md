# PHP_Laravel12_Authentication_Using_Next.JS
# Step 1 : Install Laravel 12 And Create New Project
```php
(Composer Command)
composer create-project laravel/laravel PHP_Laravel12_Authentication_Using_Next.JS
```
# Step 2 : Setup Database for.env file
```php
 DB_CONNECTION=mysql
 DB_HOST=127.0.0.1
 DB_PORT=3306
 DB_DATABASE=your database name
 DB_USERNAME=root
 DB_PASSWORD=
```

## Now Create Simple Authentication using laravel and Next.js followed all step:

# Step 3 : Create migration file for database table 
Command
```php
php artisan make:migration create_auth_table
```
File path
database/migrations/xxxx_xx_xx_create_auth_table.php
Migration Code:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth');
    }
};
```
Run migration
```php
php artisan migrate
```

# Step 4 : Create Model 
Command
```php
php artisan make:model Auth
```
File path
app/Models/Auth.php
Model Code
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
  protected $table = 'auth';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
```
# Step 5 : Create Controller 
Command
```php
php artisan make:controller AuthController
```
File path
app/Http/Controllers/AuthController.php
Controller Code
```php
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

    //  redirect to Next.js login
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

    //  redirect to Next.js dashboard
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

    //  redirect to Next.js login page
    return redirect('http://localhost:3000/login');
}

}
```
# Step 6 : Create route for routes/web.php file
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Session;

Route::get('/me', function () {
    return Session::get('auth_user');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (CSRF DISABLED ONLY FOR THESE)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::post('/login', [AuthController::class, 'login'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/logout', [AuthController::class, 'logout'])
    ->withoutMiddleware(VerifyCsrfToken::class);

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});
```
# Step 7 : Now Install Next.js and open terminal and run command
Command run :
```php
npx create-next-app next-auth-frontend
```
# Now  Show the Next.js Package successfull install 
<img width="309" height="503" alt="image" src="https://github.com/user-attachments/assets/3d302e92-0f44-46f4-93aa-724b3c3ef992" />

# Step 8 : Now Create three folder register, login and dashboard in 

# next-auth-frontend/app file
# next-auth-frontend/app/register/page.tsx
```php
"use client";

export default function Register() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold text-center mb-6">Register</h2>

       <form method="POST" action="/backend/register">
          <input
            type="text"
            name="name"
            placeholder="Name"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="email"
            name="email"
            placeholder="Email"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <button className="w-full bg-blue-600 text-white py-2 rounded">
            Register
          </button>
        </form>

        <p className="text-center mt-4 text-sm">
          Already have an account?{" "}
          <a href="/login" className="text-blue-600">Login</a>
        </p>
      </div>
    </div>
  );
}
```
# next-auth-frontend/app login/page.tsx
```php
"use client";

export default function Login() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold text-center mb-6">Login</h2>

       <form method="POST" action="/backend/login">

          <input
            type="email"
            name="email"
            placeholder="Email"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <button className="w-full bg-green-600 text-white py-2 rounded">
            Login
          </button>
          <p className="text-center mt-4 text-sm">
            Don't have an account?{" "}
            <a href="/register" className="text-blue-600">Register</a>
          </p>
        </form>
      </div>
    </div>
  );
}
```
# next-auth-frontend/app/dashboard/page.tsx
```php
"use client";

import { useEffect, useState } from "react";

export default function Dashboard() {
  const [user, setUser] = useState<any>(null);

  useEffect(() => {
    fetch("/backend/me", {
      credentials: "include",
    })
      .then(res => res.json())
      .then(data => setUser(data));
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow text-center w-96">
        <h2 className="text-2xl font-bold mb-2">
          Welcome {user?.name ?? "User"} 👋
        </h2>

        <p className="text-gray-600 mb-6">
          You are logged in successfully 🎉
        </p>

        <a
          href="/backend/logout"
          className="inline-block bg-red-600 text-white px-6 py-2 rounded"
        >
          Logout
        </a>
      </div>
    </div>
  );
}
```
# Step 9 : Now Run this project and use command for
```php
php artisan serve
```
# Step 10 : Now Open New Terminal and  select next-auth-frontend this folder :
Command:
```php
npm run build
npm run dev
```
# Step 11 : Now update for register and login and dashboard in page.tsx file in next-auth-frontend folder:
# next-auth-frontend/app/register/page.tsx
```php
"use client";

export default function Register() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold text-center mb-6">Register</h2>

       <form method="POST" action="/backend/register">
          <input
            type="text"
            name="name"
            placeholder="Name"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="email"
            name="email"
            placeholder="Email"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <button className="w-full bg-blue-600 text-white py-2 rounded">
            Register
          </button>
        </form>

        <p className="text-center mt-4 text-sm">
          Already have an account?{" "}
          <a href="/login" className="text-blue-600">Login</a>
        </p>
      </div>
    </div>
  );
}

```
# next-auth-frontend/app login/page.tsx
```php
"use client";

export default function Login() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white p-8 rounded-lg shadow">
        <h2 className="text-2xl font-bold text-center mb-6">Login</h2>

       <form method="POST" action="/backend/login">

          <input
            type="email"
            name="email"
            placeholder="Email"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            className="w-full border px-4 py-2 rounded"
          /><br/><br/>

          <button className="w-full bg-green-600 text-white py-2 rounded">
            Login
          </button>
          <p className="text-center mt-4 text-sm">
            Don't have an account?{" "}
            <a href="/register" className="text-blue-600">Register</a>
          </p>
        </form>
      </div>
    </div>
  );
}
```
# next-auth-frontend/app/dashboard/page.tsx
```php
"use client";

import { useEffect, useState } from "react";

export default function Dashboard() {
  const [user, setUser] = useState<any>(null);

  useEffect(() => {
    fetch("/backend/me", {
      credentials: "include",
    })
      .then(res => res.json())
      .then(data => setUser(data));
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow text-center w-96">
        <h2 className="text-2xl font-bold mb-2">
          Welcome {user?.name ?? "User"} 👋
        </h2>

        <p className="text-gray-600 mb-6">
          You are logged in successfully 🎉
        </p>

        <a
          href="/backend/logout"
          className="inline-block bg-red-600 text-white px-6 py-2 rounded"
        >
          Logout
        </a>
      </div>
    </div>
  );
}
```
# Now Open Browser and paste this url http://localhost:3000/register
<img width="628" height="203" alt="image" src="https://github.com/user-attachments/assets/43d21660-1afb-4599-be1e-4c13bf3e9cd8" />
<img width="628" height="199" alt="image" src="https://github.com/user-attachments/assets/e1911b1e-98ad-4bb7-9c97-b990708e6217" />
<img width="628" height="140" alt="image" src="https://github.com/user-attachments/assets/92d84653-a576-441c-a07a-88d7271f0e57" />























