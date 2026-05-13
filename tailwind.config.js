/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    'node_modules/preline/dist/*.js',
  ],
  safelist: [
    'bg-teal-100',
    'bg-amber-100',
    'bg-yellow-100',
    'bg-red-100',
    'bg-blue-100',
    'bg-gray-100',
    'bg-cyan-100',
    'bg-sky-100',
    'bg-pink-100',
    'bg-indigo-100',
    'bg-violet-100',
    'text-gray-500',
    'text-red-600',
    'text-yellow-600',
    'text-amber-600',
    'text-sky-600'
  ],
  darkMode: 'class',
  theme: {
    extend: {
      backgroundImage: {
        'custom-bg': "url('/img/dohfacade.png')",
      },
    },
    fontFamily: {
      sans: ["Inter", 'sans-serif']
    },
    container: {
      center: true,
    }
  },
  plugins: [
    require('preline/plugin'),
  ],
}

