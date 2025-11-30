/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.{html,js,php}",
    "./public/**/*.{html,js,php}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8'
        },
        secondary: {
          500: '#64748b',
          600: '#475569',
          700: '#334155'
        }
      },
      container: {
        center: true,
        padding: '1rem',
      }
    },
  },
  plugins: [
    // Add any required plugins here
  ],
}