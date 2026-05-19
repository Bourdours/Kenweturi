/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./app/Views/**/*.php",
    "./app/Libraries/**/*.php",
    "./public/**/*.html",
  ],
  safelist: [
    'animate-bounce',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans:    ['Plus Jakarta Sans', 'sans-serif'],
        display: ['Space Grotesk', 'sans-serif'],
      },
      colors: {
        paper:   'rgb(var(--color-paper) / <alpha-value>)',
        brand: {
          DEFAULT: 'rgb(var(--color-brand) / <alpha-value>)',
          dark:    'rgb(var(--color-brand-dark) / <alpha-value>)',
        },
        ink:     'rgb(var(--color-ink) / <alpha-value>)',
        action: {
          DEFAULT: 'rgb(var(--color-action) / <alpha-value>)',
          dark:    'rgb(var(--color-action-dark) / <alpha-value>)',
        },
        success: 'rgb(var(--color-success) / <alpha-value>)',
        notice:  'rgb(var(--color-notice) / <alpha-value>)',
        surface: {
          DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
          page:    'rgb(var(--color-surface-page) / <alpha-value>)',
          nav:     'rgb(var(--color-surface-nav) / <alpha-value>)',
          card:    'rgb(var(--color-surface-card) / <alpha-value>)',
          input:   'rgb(var(--color-surface-input) / <alpha-value>)',
        },
        muted:  'rgb(var(--color-muted) / <alpha-value>)',
        danger: {
          DEFAULT: 'rgb(var(--color-danger) / <alpha-value>)',
          dark:    'rgb(var(--color-danger-dark) / <alpha-value>)',
        },
        wine: {
          DEFAULT: 'rgb(var(--color-wine) / <alpha-value>)',
          dark:    'rgb(var(--color-wine-dark) / <alpha-value>)',
        },
        brown: {
          DEFAULT: 'rgb(var(--color-brown) / <alpha-value>)',
          dark:    'rgb(var(--color-brown-dark) / <alpha-value>)',
          darker:  'rgb(var(--color-brown-darker) / <alpha-value>)',
        },
      },
    },
  },
  plugins: [],
}
