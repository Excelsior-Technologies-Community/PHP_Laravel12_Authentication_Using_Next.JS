"use client";

import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import toast from "react-hot-toast";
import { useAuth } from "@/lib/auth-context";
import { apiClient } from "@/lib/api";
import { Navbar } from "@/components/navbar";
import { ThemeSwitcher } from "@/components/theme-switcher";
import { Input } from "@/components/ui/input";
import { PasswordInput } from "@/components/ui/password-input";
import { Button } from "@/components/ui/button";

const profileSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.string().email("Please enter a valid email"),
});

type ProfileForm = z.infer<typeof profileSchema>;

export default function Profile() {
  const { user, updateUser } = useAuth();

  const [loading, setLoading] = useState(false);
  const [mounted, setMounted] = useState(false);

  // =========================================================
  // EMAIL VERIFICATION STATUS
  // =========================================================
  const [emailVerified, setEmailVerified] = useState(false);
  const [verificationLoading, setVerificationLoading] = useState(true);

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<ProfileForm>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      name: "",
      email: "",
    },
  });

  // =========================================================
  // LOAD USER DATA
  // =========================================================
  useEffect(() => {
    setMounted(true);

    if (user) {
      reset({
        name: user.name,
        email: user.email,
      });
    }
  }, [user, reset]);

  // =========================================================
  // CHECK EMAIL VERIFICATION STATUS
  // =========================================================
  useEffect(() => {
    const checkVerification = async () => {
      try {
        const result = await apiClient<{
          success: boolean;
          data: {
            email_verified: boolean;
            email_verified_at: string | null;
          };
        }>("/verification/status");

        setEmailVerified(result.data.email_verified);
      } catch {
        // Ignore verification status errors
        setEmailVerified(false);
      } finally {
        setVerificationLoading(false);
      }
    };

    checkVerification();
  }, []);

  // =========================================================
  // UPDATE PROFILE
  // =========================================================
  const onSubmit = async (data: ProfileForm) => {
    setLoading(true);

    try {
      const result = await apiClient<{
        success: boolean;
        data: any;
      }>("/profile", {
        method: "PUT",
        body: JSON.stringify(data),
      });

      updateUser(result.data);

      toast.success("Profile updated successfully");
    } catch (error) {
      toast.error(
        error instanceof Error
          ? error.message
          : "Failed to update profile"
      );
    } finally {
      setLoading(false);
    }
  };

  // =========================================================
  // INITIAL MOUNT LOADING
  // =========================================================
  if (!mounted) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  // =========================================================
  // PAGE
  // =========================================================
  return (
    <div className="min-h-screen bg-background">
      <Navbar />

      <main className="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div className="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-border space-y-8">

          {/* =====================================================
              PROFILE HEADER
          ====================================================== */}
          <div>
            <h2 className="text-2xl font-bold text-foreground">
              Profile Settings
            </h2>

            <p className="text-gray-500 mt-1">
              Manage your account information
            </p>
          </div>

          {/* =====================================================
              PROFILE FORM
          ====================================================== */}
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">

            {/* NAME */}
            <Input
              label="Name"
              placeholder="Your name"
              error={errors.name?.message}
              {...register("name")}
            />

            {/* EMAIL */}
            <Input
              label="Email"
              type="email"
              placeholder="you@example.com"
              error={errors.email?.message}
              {...register("email")}
            />

            {/* =================================================
                EMAIL VERIFICATION STATUS
            ================================================== */}
            <div className="flex items-center gap-2 text-sm">

              <span className="font-medium text-foreground">
                Email status:
              </span>

              {verificationLoading ? (
                <span className="text-gray-500">
                  Checking...
                </span>
              ) : emailVerified ? (
                <span className="text-green-600 font-medium">
                  ✓ Verified
                </span>
              ) : (
                <span className="text-orange-600 font-medium">
                  ⚠ Not Verified
                </span>
              )}

            </div>

            {/* UPDATE PROFILE BUTTON */}
            <Button
              type="submit"
              loading={loading}
            >
              Update Profile
            </Button>
          </form>

          {/* =====================================================
              CHANGE PASSWORD
          ====================================================== */}
          <div className="pt-8 border-t border-border">
            <h3 className="text-lg font-semibold text-foreground mb-4">
              Change Password
            </h3>

            <ChangePasswordForm />
          </div>

          {/* =====================================================
              APPEARANCE
          ====================================================== */}
          <div className="pt-8 border-t border-border">
            <h3 className="text-lg font-semibold text-foreground mb-4">
              Appearance
            </h3>

            <ThemeSwitcher />
          </div>

        </div>
      </main>
    </div>
  );
}

// =============================================================
// CHANGE PASSWORD FORM
// =============================================================

function ChangePasswordForm() {
  const [loading, setLoading] = useState(false);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    defaultValues: {
      current_password: "",
      password: "",
      password_confirmation: "",
    },
  });

  const onSubmit = async (data: any) => {
    setLoading(true);

    try {
      await apiClient<{
        success: boolean;
        message: string;
      }>("/profile/password", {
        method: "PATCH",
        body: JSON.stringify(data),
      });

      toast.success("Password changed successfully");

      reset();
    } catch (error) {
      toast.error(
        error instanceof Error
          ? error.message
          : "Failed to change password"
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <form
      onSubmit={handleSubmit(onSubmit)}
      className="space-y-5"
    >

      {/* CURRENT PASSWORD */}
      <PasswordInput
        label="Current Password"
        placeholder="••••••••"
        error={errors.current_password?.message}
        {...register("current_password")}
      />

      {/* NEW PASSWORD */}
      <PasswordInput
        label="New Password"
        placeholder="••••••••"
        error={errors.password?.message}
        {...register("password")}
      />

      {/* CONFIRM PASSWORD */}
      <PasswordInput
        label="Confirm New Password"
        placeholder="••••••••"
        error={errors.password_confirmation?.message}
        {...register("password_confirmation")}
      />

      {/* CHANGE PASSWORD BUTTON */}
      <Button
        type="submit"
        loading={loading}
        variant="secondary"
      >
        Change Password
      </Button>

    </form>
  );
}