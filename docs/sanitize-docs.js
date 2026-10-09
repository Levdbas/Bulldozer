#!/usr/bin/env node
/**
 * Sanitizes the generated (teak) markdown files so they compile as MDX.
 *
 * Runs automatically before `npm run docs:build` (via `predocs:build`), or manually:
 *   npm run docs:sanitize
 *
 * Patterns are only applied to prose; fenced code blocks and inline code spans are left untouched.
 * All patterns are idempotent, so running the script multiple times is safe.
 */

const fs = require('fs');
const path = require('path');

/**
 * Directories (relative to this file) containing generated documentation.
 */
const TARGET_DIRS = ['hooks', 'reference'];

/**
 * Search and replace patterns to apply to all content.
 * Add new patterns here - they will be applied in order.
 */
const SEARCH_REPLACE_PATTERNS = [
   {
      search: '<br\\s*>',
      replace: '<br />',
      description: 'Replace <br> with self-closing <br />'
   },
   {
      search: '^(#{1,6}) "(wp-lemon|bulldozer|highground)',
      replace: '$1 $2',
      description: 'Remove leading quote from hook headings'
   },
   {
      search: '(?<!\\\\)\\{',
      replace: '\\{',
      description: 'Escape opening curly braces'
   },
   {
      search: '(?<!\\\\)\\}',
      replace: '\\}',
      description: 'Escape closing curly braces'
   }
];

/**
 * Apply all patterns to a piece of prose (no code).
 */
function applyPatterns(text, counts) {
   let result = text;

   for (const pattern of SEARCH_REPLACE_PATTERNS) {
      const regex = new RegExp(pattern.search, 'gm');
      result = result.replace(regex, (...args) => {
         counts[pattern.description] = (counts[pattern.description] || 0) + 1;
         return args[0].replace(new RegExp(pattern.search, 'm'), pattern.replace);
      });
   }

   return result;
}

/**
 * Apply patterns to a single line, skipping inline code spans.
 */
function sanitizeLine(line, counts) {
   return line
      .split(/(`+[^`]*?`+)/)
      .map((part) => (part.startsWith('`') ? part : applyPatterns(part, counts)))
      .join('');
}

/**
 * Apply patterns to file content, skipping fenced code blocks.
 */
function sanitizeContent(content, counts) {
   let fence = null;

   return content
      .split('\n')
      .map((line) => {
         const match = line.match(/^\s*(`{3,}|~{3,})/);

         if (match) {
            if (!fence) {
               fence = match[1];
            } else if (match[1][0] === fence[0] && match[1].length >= fence.length) {
               fence = null;
            }
            return line;
         }

         return fence ? line : sanitizeLine(line, counts);
      })
      .join('\n');
}

function collectFiles(dir) {
   if (!fs.existsSync(dir)) {
      return [];
   }

   return fs.readdirSync(dir, { withFileTypes: true }).flatMap((entry) => {
      const fullPath = path.join(dir, entry.name);

      if (entry.isDirectory()) {
         return collectFiles(fullPath);
      }

      return /\.mdx?$/.test(entry.name) ? [fullPath] : [];
   });
}

console.log('🔄 Sanitizing generated documentation files...\n');

let changedCount = 0;
let fileCount = 0;

for (const dir of TARGET_DIRS) {
   const absoluteDir = path.join(__dirname, dir);

   if (!fs.existsSync(absoluteDir)) {
      console.log(`⚠️  Skipping ${path.relative(process.cwd(), absoluteDir)} (directory not found)`);
      continue;
   }

   for (const file of collectFiles(absoluteDir)) {
      fileCount++;
      const counts = {};
      const original = fs.readFileSync(file, 'utf8');
      const sanitized = sanitizeContent(original, counts);

      if (sanitized !== original) {
         fs.writeFileSync(file, sanitized);
         changedCount++;
         console.log(`✅ ${path.relative(process.cwd(), file)}`);

         for (const [description, count] of Object.entries(counts)) {
            console.log(`   🔧 Applied: ${description} (${count} occurrence(s))`);
         }
      }
   }
}

console.log(`\n✨ Done! Checked ${fileCount} file(s), updated ${changedCount}.`);
