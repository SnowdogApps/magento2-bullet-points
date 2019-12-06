# Snowdog Magento2 Bullet Points

Extension which collects and display attributes as bullet points

### 1. Installation:

* `composer require snowdog/module-bullet-points`
* `bin/magento module:enable Snowdog_CmsApi`
* `bin/magento setup:upgrade`

### 2. Usage

The extension provides a CLI command which allows passing category IDs, attribute IDs and product IDs (this is optional) and will generate a HTML table with pairs like: `attribute label` | `attribute value` for the products assigned to the passed category IDs (if product IDs are also passed as parameter, then bullet points will be generated for these products if they're assigned to the passed category IDs). The generated table in HTML format will be set in `selling_features_bullets` attribute for each product, which can be shown in eg. product view.

```
$ bin/magento snowdog:collect:bullet-points

Usage:
  snowdog:collect:bullet-points <category-ids> <attribute-ids> [<skus>]

Arguments:
  category-ids          Category IDs
  attribute-ids         Sorted Product Attribute IDs
  skus                  Comma separated SKU list
```