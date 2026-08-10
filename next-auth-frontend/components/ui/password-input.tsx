"use client";

import { useState } from "react";
import { Eye, EyeOff } from "lucide-react";
import { cn } from "@/lib/utils";

interface PasswordInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export function PasswordInput({ label, error, className, ...props }: PasswordInputProps) {
  const [show, setShow] = useState(false);

  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-foreground mb-1.5">
          {label}
        </label>
      )}
      <div className="relative">
        <input
          {...props}
          type={show ? "text" : "password"}
          className={cn(
            "w-full px-4 py-2.5 rounded-lg border bg-white/50 backdrop-blur-sm transition-all pr-10",
            "focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary",
            "placeholder:text-gray-400",
            error ? "border-red-500 focus:ring-red-500/20 focus:border-red-500" : "border-border",
            className
          )}
        />
        <button
          type="button"
          onClick={() => setShow(!show)}
          className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
          tabIndex={-1}
        >
          {show ? <EyeOff size={18} /> : <Eye size={18} />}
        </button>
      </div>
      {error && <p className="text-red-500 text-sm mt-1.5 font-medium">{error}</p>}
    </div>
  );
}
