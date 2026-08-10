"use client";

import Link from "next/link";
import { useAuth } from "@/lib/auth-context";
import { Button } from "@/components/ui/button";

export default function Home() {
  const { user } = useAuth();

  return (
    <div className="min-h-screen flex items-center justify-center bg-background">
      <main className="flex flex-col items-center justify-center gap-8 text-center px-4">
        <div className="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center">
          <span className="text-4xl">🔐</span>
        </div>
        <div className="space-y-4">
          <h1 className="text-4xl font-bold text-foreground">
            Laravel + Next.js Auth
          </h1>
          <p className="text-lg text-gray-500 max-w-md">
            A modern authentication system with token-based API, form validation, and theme switching.
          </p>
        </div>
        <div className="flex flex-col sm:flex-row gap-4">
          {user ? (
            <Button onClick={() => window.location.href = "/dashboard"}>
              Go to Dashboard
            </Button>
          ) : (
            <>
              <Link href="/login">
                <Button>Sign In</Button>
              </Link>
              <Link href="/register">
                <Button variant="secondary">Create Account</Button>
              </Link>
            </>
          )}
        </div>
      </main>
    </div>
  );
}
