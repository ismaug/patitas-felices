/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.{html,js,php}",
    "./src/**/*.{html,js,php}",
  ],
  theme: {
    extend: {
      colors: {
        "pf-primary": "var(--primary)",
        "pf-crema": "var(--background)",
        "pf-amarillo": "var(--tertiary)",
        "pf-naranja": "var(--secondary)",
        "pf-acento": "var(--accent)",
        "pf-success": "var(--success)",
        "pf-warning": "var(--warning)",
        "pf-error": "var(--error)",
        "pf-info": "var(--info)",
        "pf-disabled": "var(--disabled)",
        "pf-text-main": "var(--text-main)",
        "pf-text-secondary": "var(--text-secondary)"
      },
      fontFamily: {
        'roboto': ['Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}