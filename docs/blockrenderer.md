# BlockRenderer Additional Documentation
In addition to the method documentation provided in the Class Reference, this document outlines some additional features and configurations available in BlockRenderer v3.

## Mark a Block as Deprecated
To mark a block as deprecated in BlockRenderer v3, you need to update the `supports` array in your block.json to include a `deprecated` key. This key should contain an associative array with the following information:

```json
"supports": {
   "mode": false,
   "align": false,
   "deprecated": {
      "use": "acf/text-and-image",
      "since": "23-05-2025"
   }
}
```

This will output  `This block is deprecated since 23-05-2025. Please replace this block in favor of %2$s acf/text-and-image.` when the block is displayed in the editor.

## Add disable button to Block
To add a disable button to a block in BlockRenderer v3, you need to include the `showDisableButton` key in the `supports` array of your block.json file.

```json
"supports": {
   "mode": false,
   "align": false,
   "showDisableButton": true,
}
```
This will add a disable button to the ACF fields of the block in the editor. When set to `true`, it will disable the block and display a message in the backend indicating that the block is disabled. In the frontend it will render with the additional class `acf-block--disabled` that will set the visibility to `hidden`.