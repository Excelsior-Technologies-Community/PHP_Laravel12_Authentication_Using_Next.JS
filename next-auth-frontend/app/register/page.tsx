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
