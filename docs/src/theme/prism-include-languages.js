import siteConfig from '@generated/docusaurus.config';
import twigLanguage from '../prism-twig';

export default function prismIncludeLanguages(PrismObject) {
  const {
    themeConfig: { prism },
  } = siteConfig;
  const { additionalLanguages } = prism;
  // Prism components work on the Prism instance on the window, while prism-
  // react-renderer uses its own Prism instance. We temporarily mount the
  // instance onto window, import components to enhance it, then remove it to
  // avoid polluting global namespace.
  // You can mutate PrismObject: registering plugins, deleting languages... As
  // long as you don't re-assign it
  const PrismBefore = globalThis.Prism;
  globalThis.Prism = PrismObject;

  require('prismjs/components/prism-markup-templating.js');
  require(`prismjs/components/prism-php.js`);
  require(`prismjs/components/prism-bash.js`);
  require(`prismjs/components/prism-scss.js`);

  // Use our custom enhanced Twig syntax definition
  twigLanguage(PrismObject);

  // Clean up and eventually restore former globalThis.Prism object (if any)
  // delete globalThis.Prism;
  // if (typeof PrismBefore !== 'undefined') {
  //   globalThis.Prism = PrismObject;
  // }
}
