/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/Views/**/*.php',
    './public/**/*.html',
    './public/assets/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        'atlex-red': '#E53935',
        'atlex-blue': '#003366',
        'atlex-dark': '#001a3d',
        'atlex-bg': '#0a0e1a',
        'atlex-beige': '#D7B899'
      },
      fontFamily: {
        bebas: ['"Bebas Neue"', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
        poppins: ['Poppins', 'sans-serif']
      }
    }
  },
  plugins: []
};
