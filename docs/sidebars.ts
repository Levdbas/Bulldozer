import type { SidebarsConfig } from '@docusaurus/plugin-content-docs';

const sidebars: SidebarsConfig = {
  docsSidebar: [
    'introduction',
    'wp-cli-commands',
    {
      type: 'category',
      label: 'BlockRenderer',
      items: ['blockrenderer', 'blockrendered-v2-to-v3'],
    },
    {
      type: 'category',
      label: 'Hooks',
      items: ['hooks/actions', 'hooks/filters'],
    },
    {
      type: 'category',
      label: 'Class Reference',
      items: [
        'reference/highground-bulldozer-asset',
        'reference/highground-bulldozer-autoloader',
        'reference/highground-bulldozer-blockrendererv1',
        'reference/highground-bulldozer-blockrendererv2',
        'reference/highground-bulldozer-site_icons',
      ],
    },
  ],
};

export default sidebars;