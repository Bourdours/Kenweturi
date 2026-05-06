/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./app/Libraries/**/*.php",
    "./public/**/*.html",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans:    ['Plus Jakarta Sans', 'sans-serif'],
        display: ['Space Grotesk', 'sans-serif'],
      },
      colors: {
        primary: {
          DEFAULT: '#204A81',
          dark:    '#283C7D',
          darker:  '#1D2B59',
          light:   '#345076',
          muted:   '#4A576A',
        },
        accent: {
          DEFAULT: '#F2860E',
          dark:    '#E37413',
          muted:   '#C27C27',
          light:   '#FFA800',
        },
        danger: {
          DEFAULT: '#D80B1C',
          dark:    '#b5091a',
        },
        wine: {
          DEFAULT: '#872249',
          dark:    '#6E2957',
        },
        brown: {
          DEFAULT: '#716354',
          dark:    '#7C4210',
          darker:  '#47290F',
        },
        ink: '#0E0D0D',
      },
    },
  },
  plugins: [],
}
