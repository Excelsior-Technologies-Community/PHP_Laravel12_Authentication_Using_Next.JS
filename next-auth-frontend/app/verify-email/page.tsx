"use client";

import { useEffect, useState } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import Link from "next/link";

export default function VerifyEmail() {
  const searchParams = useSearchParams();
  const router = useRouter();

  const status = searchParams.get("status");

  const [message, setMessage] = useState(
    "Checking your email verification..."
  );

  useEffect(() => {
    if (status === "success") {
      setMessage(
        "Your email has been verified successfully!"
      );
    } else if (status === "already") {
      setMessage(
        "Your email address is already verified."
      );
    } else if (status === "invalid") {
      setMessage(
        "This verification link is invalid or expired."
      );
    }
  }, [status]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-background px-4">
      <div className="w-full max-w-md bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-border text-center">

        <div className="text-5xl mb-5">
          {status === "success"
            ? "✅"
            : status === "invalid"
            ? "❌"
            : "📧"}
        </div>

        <h1 className="text-2xl font-bold text-foreground mb-3">
          Email Verification
        </h1>

        <p className="text-gray-500 mb-8">
          {message}
        </p>

        {status === "success" && (
          <button
            onClick={() => router.push("/login")}
            className="w-full bg-primary text-primary-foreground py-2.5 rounded-lg font-medium hover:opacity-90"
          >
            Continue to Login
          </button>
        )}

        {status === "already" && (
          <Link
            href="/login"
            className="inline-block w-full bg-primary text-primary-foreground py-2.5 rounded-lg font-medium hover:opacity-90"
          >
            Go to Login
          </Link>
        )}

        {status === "invalid" && (
          <Link
            href="/login"
            className="inline-block w-full bg-primary text-primary-foreground py-2.5 rounded-lg font-medium hover:opacity-90"
          >
            Back to Login
          </Link>
        )}
      </div>
    </div>
  );
}