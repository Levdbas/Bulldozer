var el = wp.element.createElement;

var withClientClassName = wp.compose.createHigherOrderComponent(function (
   BlockListBlock
) {
   return function (props) {
      var newProps = null;


      // if props.name contains acf/ then add a class to the block

      if (props.name.includes('acf/')) {
         const name = props.name.replace('acf/', '');

         var newProps = lodash.assign({}, props, {
            className: name,
         });
      }
      return el(BlockListBlock, newProps);
   };
}, 'withClientClassName');


wp.hooks.addFilter(
   'editor.BlockListBlock',
   'gdt-guten-plugin/add-block-class-name',
   withClientClassName,
   30
);