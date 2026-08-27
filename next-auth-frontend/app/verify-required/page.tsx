"use client";

import Link from "next/link";

export default function VerifyRequired() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-background px-4">
      <div className="w-full max-w-md bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-border text-center">

        <div className="text-5xl mb-5">
          📧
        </div>

        <h1 className="text-3xl font-bold text-foreground">
          Verify Your Email
        </h1>

        <p className="text-gray-500 mt-3 mb-6">
          Your account has been created successfully.
        </p>

        <div className="rounded-lg bg-blue-50 border border-blue-200 p-4 text-blue-700 text-sm mb-6">
          We've sent a verification link to your email address.
          Please verify your email before logging in.
        </div>

        <Link
          href="/login"
          className="inline-block w-full bg-primary text-primary-foreground py-2.5 rounded-lg font-medium hover:opacity-90"
        >
          Go to Login
        </Link>
      </div>
    </div>
  );
}