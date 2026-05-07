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
          label:   '#6fa3cc',
          subtle:  '#93b8d8',
        },
        accent: {
          DEFAULT: '#D85A30',
          dark:    '#c24e28',
          bright:  '#F2860E',
          light:   '#FFA800',
          muted:   '#C27C27',
        },
        surface: {
          DEFAULT: '#111a26',
          page:    '#0f2744',
          nav:     '#1a3a5c',
          card:    '#16222e',
          input:   '#1a2d40',
        },
        muted:  '#888888',
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
