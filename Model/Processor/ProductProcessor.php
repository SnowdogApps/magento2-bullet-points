<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DB\Select;

class ProductProcessor extends AbstractProcessor
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->productRepository = $productRepository;

        parent::__construct($resourceConnection);
    }

    /***
     * @param array $categoryIds
     * @param array $skus
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getProductsIds($categoryIds = [], $skus = []): array
    {
        $productIdsWithCategoryId = $productIds = [];

        $select = $this->getConnection()->select()->from(
            ['category_product' => $this->getResourceConnection()->getTableName('catalog_category_product')],
            ['product_id', 'category_id']
        )->join(
            ['product_table' => $this->getResourceConnection()->getTableName('catalog_product_entity')],
            'product_table.entity_id = category_product.product_id'
        );

        if (!empty($categoryIds)) {
            $select->where('category_product.category_id IN (?)', $categoryIds);
        }

        if (!empty($skus)) {
            $select->where('product_table.sku IN (?)', $skus);
        }

        $rows = $this->getConnection()->query($select)->fetchAll();
        foreach ($rows as $row) {
            $productIdsWithCategoryId[$row['category_id']][] = $row['product_id'];
            $productIds[$row['product_id']] = $row['product_id'];
        }

        return [
            'product_ids_with_category_id' => $productIdsWithCategoryId,
            'product_ids' => $productIds
        ];
    }

    public function getProductAttributesData($product, $attributes)
    {
        $data = [];
        foreach ($attributes as $attribute) {
            $attributeFrontend = $product->getResource()->getAttribute($attribute['attribute_code'])->getFrontend();
            $data[] = [
                'label' => $attributeFrontend->getLabel(),
                'value' => $attributeFrontend->getValue($product)
            ];
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $attributeValue
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function updateProductAttributeValue($product, $attributeValue): void
    {
        $product->setData('selling_features_bullets', $attributeValue);
        $this->productRepository->save($product);
    }

    /**
     * @param Select $select
     * @param int $storeId
     * @param int $attributeId
     * @param string $attributeCode
     * @param string $backendType
     * @return void
     */
    private function addAttributeToSelect(Select $select, $storeId, $attributeId, $attributeCode, $backendType): void
    {
        $defTableName = 'def_' . $attributeCode . '_table';
        $storeTableName = 'store_' . $attributeCode . '_table';

        $select->joinLeft(
            [$defTableName => $this->getResourceConnection()->getTableName('catalog_product_entity_' . $backendType)],
            implode(
                ' AND ',
                [
                    "main_table.entity_id = " . $defTableName . ".entity_id",
                    $this->getConnection()->quoteInto(
                        $defTableName . ".attribute_id = ?",
                        $attributeId
                    ),
                    $defTableName . ".store_id = 0"
                ]
            ),
            []
        );
        $select->joinLeft(
            [$storeTableName => $this->getResourceConnection()->getTableName('catalog_product_entity_varchar')],
            implode(
                ' AND ',
                [
                    "main_table.entity_id = " . $storeTableName . ".entity_id",
                    $this->getConnection()->quoteInto(
                        $storeTableName . ".attribute_id = ?",
                        $attributeId
                    ),
                    $this->getConnection()->quoteInto(
                        $storeTableName . ".store_id = ?",
                        $storeId
                    ),
                ]
            ),
            []
        );

        $imageValueExpr = $this->getConnection()->getCheckSql(
            $storeTableName . '.value_id IS NULL',
            $defTableName . '.value',
            $storeTableName . '.value'
        );

        $select->columns([$attributeCode => $imageValueExpr]);
    }
}
