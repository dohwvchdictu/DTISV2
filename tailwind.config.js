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
    'dark:bg-neutral-700',
    'dark:bg-red-500/20',
    'dark:bg-yellow-500/20',
    'dark:bg-amber-500/20',
    'dark:bg-sky-500/20',
    'dark:bg-gray-500/20',
    'dark:bg-teal-500/20',
    'dark:bg-blue-500/20',
    'dark:bg-cyan-500/20',
    'dark:bg-pink-500/20',
    'dark:bg-indigo-500/20',
    'dark:bg-violet-500/20',
    'dark:bg-emerald-500/20',
    'dark:text-neutral-400',
    'dark:text-red-400',
    'dark:text-yellow-400',
    'dark:text-amber-400',
    'dark:text-sky-400',
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

