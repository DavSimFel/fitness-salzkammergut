/** @type {import('tailwindcss').Config} */
const palette = {
  'almost-black': 'var(--wp--preset--color--almost-black)',
  'black-90': 'var(--wp--preset--color--black-90)',
  'black-80': 'var(--wp--preset--color--black-80)',
  'grey': 'var(--wp--preset--color--grey)',
  'black-60': 'var(--wp--preset--color--black-60)',
  'black-50': 'var(--wp--preset--color--black-50)',
  'black-40': 'var(--wp--preset--color--black-40)',
  'black-30': 'var(--wp--preset--color--black-30)',
  'black-20': 'var(--wp--preset--color--black-20)',
  'soft-white': 'var(--wp--preset--color--soft-white)',
  'lime': 'var(--wp--preset--color--lime)',
  'lime-dark': 'var(--wp--preset--color--lime-dark)',
  'lime-light': 'var(--wp--preset--color--lime-light)',
  'sport-performance': 'var(--wp--preset--color--sport-performance)',
  'kraft-muskelaufbau': 'var(--wp--preset--color--kraft-muskelaufbau)',
  'reha-praevention': 'var(--wp--preset--color--reha-praevention)',
  'abnehmen-wohlfuehlen': 'var(--wp--preset--color--abnehmen-wohlfuehlen)',
  'fitness-gesundheit': 'var(--wp--preset--color--fitness-gesundheit)',
};

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.{php,html,json}',
    './parts/**/*.{html,php}',
    './patterns/**/*.{php,html}',
    './templates/**/*.{html,php}',
    './specs/**/*.{html,md}',
    './inc/**/*.{php,html}',
  ],
  prefix: 'tw-',
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      colors: palette,
      fontFamily: {
        sans: ['var(--wp--preset--font-family--work-sans)', 'sans-serif'],
        serif: ['var(--wp--preset--font-family--tiempos-text)', 'serif'],
      },
      borderRadius: {
        '2.5xl': '1.25rem',
        'card': '0.625rem',
      },
      boxShadow: {
        card: '0 14px 50px -20px rgba(0, 0, 0, 0.25)',
      },
      spacing: {
        15: '3.75rem',
      },
    },
  },
  plugins: [],
};
