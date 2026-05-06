/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{php,js}"],
  theme: {
    fontfamily:{
      'sans':['Roboto','sans-serif']
    },
    extend: {
      backgroundImage:{
        "home": "url('/assets/bg.png')"
      }
    },
  },
  plugins: [],
}

