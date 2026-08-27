"use client";

import { useState } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import toast from "react-hot-toast";
import Link from "next/link";

import { Input } from "@/components/ui/input";
import { PasswordInput } from "@/components/ui/password-input";
import { Button } from "@/components/ui/button";

const schema = z
  .object({
    email: z.string().email("Please enter a valid email"),
    password: z
      .string()
      .min(8, "Password must be at least 8 characters"),
    password_confirmation: z
      .string()
      .min(1, "Please confirm your password"),
  })
  .refine(
    (data) => data.password === data.password_confirmation,
    {
      message: "Passwords do not match",
      path: ["password_confirmation"],
    }
  );

type FormData = z.infer<typeof schema>;

export default function ResetPassword() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const token = searchParams.get("token") || "";
  const emailFromUrl = searchParams.get("email") || "";

  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      email: emailFromUrl,
    },
  });

  const onSubmit = async (data: FormData) => {
    if (!token) {
      toast.error("Invalid or missing reset token.");
      return;
    }

    setLoading(true);

    try {
      const res = await fetch(
        "http://localhost:8000/api/reset-password",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            token,
            email: data.email,
            password: data.password,
            password_confirmation:
              data.password_confirmation,
          }),
        }
      );

      const result = await res.json();

      if (!res.ok) {
        toast.error(
          result.message || "Password reset failed"
        );
        return;
      }

      setSuccess(true);

      toast.success(
        "Password reset successfully!"
      );

      setTimeout(() => {
        router.push("/login");
      }, 1500);
    } catch {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-background px-4">
      <div className="w-full max-w-md bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-border">

        <div className="text-center mb-8">
          <div className="text-4xl mb-3">
            🔐
          </div>

          <h1 className="text-3xl font-bold text-foreground">
            Reset Password
          </h1>

          <p className="text-gray-500 mt-2">
            Create a new secure password.
          </p>
        </div>

        {success ? (
          <div className="text-center space-y-4">
            <div className="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700">
              Password reset successfully.
            </div>

            <p className="text-sm text-gray-500">
              Redirecting you to login...
            </p>
          </div>
        ) : (
          <form
            onSubmit={handleSubmit(onSubmit)}
            className="space-y-5"
          >
            <Input
              label="Email"
              type="email"
              placeholder="you@example.com"
              error={errors.email?.message}
              {...register("email")}
            />

            <PasswordInput
              label="New Password"
              placeholder="••••••••"
              error={errors.password?.message}
              {...register("password")}
            />

            <PasswordInput
              label="Confirm New Password"
              placeholder="••••••••"
              error={
                errors.password_confirmation?.message
              }
              {...register("password_confirmation")}
            />

            <Button
              type="submit"
              loading={loading}
              className="w-full"
            >
              Reset Password
            </Button>
          </form>
        )}

        {!success && (
          <p className="text-center mt-6 text-sm text-gray-600">
            Remember your password?{" "}
            <Link
              href="/login"
              className="text-primary font-medium hover:underline"
            >
              Back to Login
            </Link>
          </p>
        )}
      </div>
    </div>
  );
}