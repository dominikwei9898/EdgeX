/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        // Jay Cutler 主题颜色
        'cutler-black': 'rgb(0, 0, 0)',
        'cutler-dark': 'rgb(26, 26, 26)',
        'cutler-dark-gray': '#2d2d2d',
        'cutler-orange': '#ff6b35',
        'cutler-gold': '#ffa500',
        'cutler-orange-dark': '#e55a2b',
        'cutler-accent-blue': 'rgb(51, 79, 180)',
        'cutler-bg-black': 'rgb(0, 0, 0)',
        'cutler-bg-dark': 'rgb(26, 26, 26)',
        'cutler-bg-light': 'rgb(243, 243, 243)',
        'cutler-bg-white': '#ffffff',
        'cutler-bg-gray': '#f5f5f5',
        'cutler-text-dark': 'rgb(26, 26, 26)',
        'cutler-text-gray': '#666666',
        'cutler-text-light': 'rgb(255, 255, 255)',
        'cutler-border-light': '#e5e5e5',
        'cutler-border-gray': '#d0d0d0',
        'cutler-border-dark': '#333333',
      },
      screens: {
        'xs': '480px',
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1200px',
        '2xl': '1536px',
      },
    },
  },
  plugins: [],
}

