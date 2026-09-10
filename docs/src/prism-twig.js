/**
 * Enhanced Twig syntax highlighting for Prism
 * Supports Twig templating syntax with comprehensive keyword coverage
 */
export default function (Prism) {
   Prism.languages.twig = {
      'comment': {
         pattern: /\{#[\s\S]*?#\}/,
         greedy: true
      },
      'tag-name': {
         pattern: /(^\{%-?\s*)\w+/,
         lookbehind: true,
         alias: 'keyword'
      },
      'delimiter': {
         pattern: /^\{[{%]-?|-?[%}]\}$/,
         alias: 'punctuation'
      },
      'string': [
         {
            pattern: /"([^"\\]|\\.)*"/,
            greedy: true,
         },
         {
            pattern: /'([^'\\]|\\.)*'/,
            greedy: true,
         }
      ],
      'keyword': /\b(?:apply|autoescape|block|cache|deprecated|do|embed|extends|filter|flush|for|from|if|import|include|macro|sandbox|set|use|verbatim|with|endapply|endautoescape|endblock|endcache|endembed|endfilter|endfor|endif|endmacro|endsandbox|endset|endwith|as|else|elseif|and|or|not|in|is|b-and|b-or|b-xor|matches|starts|ends|defined|divisible|empty|even|iterable|odd|same|constant|null|none)\b/,
      'boolean': /\b(?:true|false|TRUE|FALSE)\b/,
      'null': {
         pattern: /\b(?:null|NULL|none|NONE)\b/,
         alias: 'keyword'
      },
      'number': /\b0x[\dA-Fa-f]+|(?:\b\d+(?:\.\d*)?|\B\.\d+)(?:[Ee][-+]?\d+)?/,
      'function': [
         {
            pattern: /\b(?:abs|attribute|batch|capitalize|column|convert_encoding|country_name|country_timezones|currency_name|currency_symbol|data_uri|date|date_modify|default|escape|e|filter|first|format|format_currency|format_date|format_datetime|format_number|format_time|html_to_markdown|inline_css|inky_to_html|join|json_encode|keys|language_name|last|length|locale_name|lower|map|markdown_to_html|merge|nl2br|number_format|raw|reduce|replace|reverse|round|slice|slug|sort|spaceless|split|striptags|timezone_name|title|trim|u|upper|url_encode|abbr_class|abbr_method|class|constant|cycle|date|divisible|dump|include|max|min|parent|random|range|source|template_from_string)\b(?=\()/,
            alias: 'builtin'
         },
         /\b[a-zA-Z_]\w*(?=\()/
      ],
      'filter': {
         pattern: /(\|)\s*[a-zA-Z_]\w*/,
         lookbehind: true,
         alias: 'function'
      },
      'test': {
         pattern: /(\bis\s+(?:not\s+)?)[a-zA-Z_]\w*/,
         lookbehind: true,
         alias: 'function'
      },
      'property': {
         pattern: /\b[a-zA-Z_]\w*(?=\s*\.)/,
         alias: 'variable'
      },
      'variable': /\b[a-zA-Z_]\w*\b/,
      'operator': [
         {
            pattern: /(\s)(?:and|b-and|b-or|b-xor|ends with|in|is|matches|not|or|same as|starts with)(?=\s)/,
            lookbehind: true
         },
         /[=<>]=?|!=|\*\*?|\/\/?|\?:?|[-+~%|]|\.\./
      ],
      'punctuation': /[()\[\]{}:.,|]/
   };

   // Hook into markup-templating for proper Twig block handling
   Prism.hooks.add('before-tokenize', function (env) {
      if (env.language !== 'twig') {
         return;
      }
      // Match Twig delimiters: {# comments #}, {% statements %}, {{ expressions }}
      var pattern = /\{(?:#[\s\S]*?#|%[\s\S]*?%|\{[\s\S]*?\})\}/g;
      Prism.languages['markup-templating'].buildPlaceholders(env, 'twig', pattern);
   });

   Prism.hooks.add('after-tokenize', function (env) {
      Prism.languages['markup-templating'].tokenizePlaceholders(env, 'twig');
   });

   // Support Twig inside HTML
   if (Prism.languages.markup) {
      Prism.languages.insertBefore('markup', 'tag', {
         'twig': {
            pattern: /\{(?:#[\s\S]*?#|%[\s\S]*?%|\{[\s\S]*?\})\}/,
            inside: Prism.languages.twig
         }
      });
   }
}
