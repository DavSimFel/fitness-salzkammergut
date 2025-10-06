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

const enableFullBuild = process.env.TAILWIND_FULL === '1';
// Allow broader utility coverage for editor experiments without bloating production builds.

const escapeForRegex = (value) => value.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');
const toAlternation = (values) => values.map(escapeForRegex).join('|');

const paletteSlugs = Object.keys(palette);
const spacingScale = [
  '0',
  '0.5',
  '1',
  '1.5',
  '2',
  '2.5',
  '3',
  '3.5',
  '4',
  '5',
  '6',
  '7',
  '8',
  '9',
  '10',
  '11',
  '12',
  '14',
  '16',
  '20',
  '24',
  '28',
  '32',
  '36',
  '40',
  '44',
  '48',
  '52',
  '56',
  '60',
  '64',
  '72',
  '80',
  '96',
  'px',
];
const fractionScale = [
  '1/2',
  '1/3',
  '2/3',
  '1/4',
  '3/4',
  '1/5',
  '2/5',
  '3/5',
  '4/5',
  '1/6',
  '2/6',
  '3/6',
  '4/6',
  '5/6',
  '1/12',
  '2/12',
  '3/12',
  '4/12',
  '5/12',
  '6/12',
  '7/12',
  '8/12',
  '9/12',
  '10/12',
  '11/12',
];
const maxWidthScale = [
  'none',
  'xs',
  'sm',
  'md',
  'lg',
  'xl',
  '2xl',
  '3xl',
  '4xl',
  '5xl',
  '6xl',
  '7xl',
  'prose',
  'screen-sm',
  'screen-md',
  'screen-lg',
  'screen-xl',
  'screen-2xl',
];
const dimensionExtras = ['auto', 'full', 'screen', 'min', 'max', 'fit'];
const responsiveVariants = ['sm', 'md', 'lg', 'xl', '2xl'];

const runtimeSafelist = [
  'is-menu-open',
  'has-modal-open',
  'wp-block-navigation__responsive-container',
  'wp-block-navigation__container',
  'wp-block-navigation-item',
  'wp-block-navigation-item__content',
];

const buildSafelist = () => {
  if (!enableFullBuild) return [];

  const dimensionValues = Array.from(
    new Set([...spacingScale, ...fractionScale, ...dimensionExtras])
  );

  // Target the most common utility families instead of the entire framework to keep memory in check.
  return [
    {
      pattern: new RegExp(
        `^tw-(?:bg|text|border|fill|stroke)-(?:${toAlternation(paletteSlugs)})$`
      ),
      variants: responsiveVariants,
    },
    {
      pattern: new RegExp(
        `^tw-(?:p|px|py|pt|pr|pb|pl|m|mx|my|mt|mr|mb|ml|gap|gap-x|gap-y|space-x|space-y)-(?:${toAlternation(spacingScale)})$`
      ),
      variants: responsiveVariants,
    },
    {
      pattern: new RegExp(
        `^tw-(?:w|h|min-w|min-h|max-h)-(?:${toAlternation(dimensionValues)})$`
      ),
      variants: responsiveVariants,
    },
    {
      pattern: new RegExp(
        `^tw-(?:max-w)-(?:${toAlternation([...maxWidthScale, ...dimensionExtras])})$`
      ),
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-grid-cols-(?:1|2|3|4|5|6|8|12)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-col-span-(?:1|2|3|4|5|6|7|8|9|10|11|12)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-row-span-(?:1|2|3|4|5|6)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-rounded(?:-(?:none|sm|md|lg|xl|2xl|3xl|full|card|2\.5xl))?$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-shadow(?:-(?:sm|md|lg|xl|2xl|inner|none|card))?$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-z-(?:0|10|20|30|40|50|auto)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-(?:static|fixed|absolute|relative|sticky)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-(?:flex|inline-flex|grid|inline-grid|block|inline-block|hidden|contents)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-flex-(?:row|row-reverse|col|col-reverse|wrap|wrap-reverse|nowrap|1|auto|initial|none)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-(?:items|justify|content|self)-(?:start|end|center|between|around|evenly|stretch|baseline)$/,
      variants: responsiveVariants,
    },
    {
      pattern: /^tw-(?:place-items|place-content)-(?:start|end|center|between|around|evenly|stretch)$/,
      variants: responsiveVariants,
    },
  ];
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
  safelist: [...buildSafelist(), ...runtimeSafelist],
  theme: {
    extend: {
      colors: palette,
      fontFamily: {
        sans: ['var(--wp--preset--font-family--work-sans)', 'sans-serif'],
        serif: ['var(--wp--preset--font-family--tiempos-text)', 'serif'],
      },
      borderRadius: {
        '2.5xl': '1.25rem',
        card: '0.625rem',
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
