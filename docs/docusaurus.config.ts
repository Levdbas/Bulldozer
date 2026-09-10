import path from 'node:path';
import type { Config } from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

const config: Config = {
  staticDirectories: [path.resolve(__dirname, 'static')],
  title: 'Bulldozer Documentation',
  tagline: 'Classes to extend your Timber-based WordPress theme',
  favicon: 'img/icon.svg',
  url: 'https://levdbas.github.io',
  baseUrl: '/Bulldozer/',
  organizationName: 'Levdbas',
  projectName: 'Bulldozer',
  trailingSlash: false,
  onBrokenLinks: 'warn',
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },
  themes: [
    [
      require.resolve('@easyops-cn/docusaurus-search-local'),
      {
        hashed: true,
      },
    ],
  ],
  presets: [
    [
      'classic',
      {
        docs: {
          path: path.resolve(__dirname),
          routeBasePath: '/',
          sidebarPath: path.resolve(__dirname, 'sidebars.ts'),
          editUrl: 'https://github.com/Levdbas/Bulldozer/tree/main/',
        },
        blog: false,
        theme: {
          customCss: path.resolve(__dirname, 'src/css/custom.css'),
        },
      } satisfies Preset.Options,
    ],
  ],
  themeConfig: {
    image: 'img/icon.svg',
    navbar: {
      title: 'Bulldozer',
      logo: {
        alt: 'Bulldozer logo',
        src: 'img/icon.svg',
      },
      items: [
        {
          type: 'docSidebar',
          sidebarId: 'docsSidebar',
          position: 'left',
          label: 'Docs',
        },
        {
          href: 'https://github.com/Levdbas/Bulldozer',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
  } satisfies Preset.ThemeConfig,
};

export default config;