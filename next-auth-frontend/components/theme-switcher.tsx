"use client";

import { useTheme } from "@/lib/theme-context";

const themes = [
  { id: "default", name: "Default", color: "bg-blue-600" },
  { id: "dark", name: "Dark", color: "bg-gray-900" },
  { id: "emerald", name: "Emerald", color: "bg-emerald-600" },
  { id: "purple", name: "Purple", color: "bg-purple-600" },
  { id: "orange", name: "Orange", color: "bg-orange-600" },
] as const;

export function ThemeSwitcher() {
  const { theme, setTheme } = useTheme();

  return (
    <div className="space-y-3">
      <label className="block text-sm font-medium text-foreground">Theme</label>
      <div className="flex flex-wrap gap-3">
        {themes.map((t) => (
          <button
            key={t.id}
            onClick={() => setTheme(t.id)}
            className={`flex items-center gap-2 px-4 py-2 rounded-lg border-2 transition-all ${
              theme === t.id
                ? "border-primary bg-primary/10 shadow-sm"
                : "border-border hover:border-gray-300"
            }`}
          >
            <span className={`w-4 h-4 rounded-full ${t.color}`} />
            <span className="text-sm font-medium text-foreground">{t.name}</span>
          </button>
        ))}
      </div>
    </div>
  );
}
