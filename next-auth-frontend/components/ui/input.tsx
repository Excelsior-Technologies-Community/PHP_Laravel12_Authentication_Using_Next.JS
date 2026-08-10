"use client";

import { cn } from "@/lib/utils";

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export function Input({ label, error, className, ...props }: InputProps) {
  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-foreground mb-1.5">
          {label}
        </label>
      )}
      <input
        {...props}
        className={cn(
          "w-full px-4 py-2.5 rounded-lg border bg-white/50 backdrop-blur-sm transition-all",
          "focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary",
          "placeholder:text-gray-400",
          error ? "border-red-500 focus:ring-red-500/20 focus:border-red-500" : "border-border",
          className
        )}
      />
      {error && <p className="text-red-500 text-sm mt-1.5 font-medium">{error}</p>}
    </div>
  );
}
