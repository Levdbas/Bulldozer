// Our filter function
function setBlockCustomClassName(className, blockName) {

   if (blockName.includes('acf/')) {
      const name = blockName.replace('acf/', '');
      return name;
   }

   return className;
}

// Adding the filter
wp.hooks.addFilter(
   'blocks.getBlockDefaultClassName',
   'my-plugin/set-block-custom-class-name',
   setBlockCustomClassName
);
