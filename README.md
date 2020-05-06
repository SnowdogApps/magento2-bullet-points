# Snowdog Magento2 Bullet Points

Extension which collects and display attributes as bullet points

### 1. Installation:

* `composer require snowdog/module-bullet-points`
* `bin/magento module:enable Snowdog_BulletPoints`
* `bin/magento setup:upgrade`

### 2. Usage

The extension provides a CLI command which allows passing category IDs, attribute IDs and product IDs (this is optional) and will generate a HTML table with pairs like: `attribute label` | `attribute value` for the products assigned to the passed category IDs (if product IDs are also passed as parameter, then bullet points will be generated for these products if they're assigned to the passed category IDs). The generated table in HTML format will be set in `selling_features_bullets` attribute for each product, which can be shown in eg. product view.
This module will generate HTML table based on attribute_ids ordering and will add 
`<attribute_code>_label` class for `dt` and `<attribute_code>_value` class for `dd` tags.

```
$ bin/magento snowdog:collect:bullet-points

Usage:
  snowdog:collect:bullet-points <category-ids> <attribute-ids> [<skus>]

Arguments:
  category-ids          Category IDs
  attribute-ids         Sorted Product Attribute IDs
  skus                  Comma separated SKU list
```
