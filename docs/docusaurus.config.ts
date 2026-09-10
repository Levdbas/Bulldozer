import path from 'node:path';
import { themes as prismThemes } from 'prism-react-renderer';
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
    }, footer: {
      style: 'dark',
      links: [
        {
          title: 'Learn More',
          items: [
            {
              label: 'wp-lemon docs',
              href: 'https://studio-lemon.github.io/wp-lemon-docs/docs/',
            },
            {
              label: 'Timber',
              href: 'https://timber.github.io/docs/',
            },
            {
              label: 'ACF Builder',
              href: 'https://github.com/StoutLogic/acf-builder/wiki/',
            },
            {
              label: 'Bedrock',
              href: 'https://github.com/roots/bedrock',
            },
            {
              label: 'Bootstrap',
              href: 'https://getbootstrap.com/docs/5.3/getting-started/introduction/',
            },
          ],
        },
        {
          title: 'Community',
          items: [
            {
              label: 'GitHub',
              href: 'https://github.com/Studio-Lemon/wp-lemon-docs',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} wp-lemon. Built with Docusaurus.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
      additionalLanguages: ['php', 'bash', 'scss'],
    },
  } satisfies Preset.ThemeConfig,
};

export default config;