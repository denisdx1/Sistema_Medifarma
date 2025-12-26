/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: "var(--primary)",
        secondary: "var(--secondary)",
        secondaryLight: "var(--secondary-light)",
        secondaryLighter: "var(--secondary-lighter)",
        secondaryMuted: "var(--secondary-muted)",
        secondaryPurple: "var(--secondary-purple)",
      },
    },
  },
  plugins: [],
}
