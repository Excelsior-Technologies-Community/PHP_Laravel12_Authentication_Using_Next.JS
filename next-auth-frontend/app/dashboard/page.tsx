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
