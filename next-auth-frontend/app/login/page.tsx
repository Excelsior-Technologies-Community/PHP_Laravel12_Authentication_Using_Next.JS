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
