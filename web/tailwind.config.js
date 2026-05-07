/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      colors: {
        theme: {
          50: 'rgb(var(--color-theme-50) / <alpha-value>)',
          100: 'rgb(var(--color-theme-100) / <alpha-value>)',
          200: 'rgb(var(--color-theme-200) / <alpha-value>)',
          300: 'rgb(var(--color-theme-300) / <alpha-value>)',
          400: 'rgb(var(--color-theme-400) / <alpha-value>)',
          500: 'rgb(var(--color-theme-500) / <alpha-value>)',
          600: 'rgb(var(--color-theme-600) / <alpha-value>)',
          700: 'rgb(var(--color-theme-700) / <alpha-value>)',
          800: 'rgb(var(--color-theme-800) / <alpha-value>)',
          900: 'rgb(var(--color-theme-900) / <alpha-value>)',
        },
        sidebar: {
          DEFAULT: 'rgb(var(--color-sidebar) / <alpha-value>)',
          hover: 'rgb(var(--color-sidebar-hover) / <alpha-value>)',
          border: 'rgb(var(--color-sidebar-border) / var(--color-sidebar-border-alpha))',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
