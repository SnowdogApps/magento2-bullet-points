<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
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
    ) {
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

    /**
     * @param ProductInterface $product
     * @param array $attributes
     * @return array
     */
    public function getProductAttributesData($product, $attributes): array
    {
        $data = [];
        foreach ($attributes as $attribute) {
            $attributeFrontend = $product->getResource()->getAttribute($attribute['attribute_code'])->getFrontend();
            $data[$attribute['attribute_id']] = [
                'code' => $attribute['attribute_code'],
                'label' => $attributeFrontend->getLabel(),
                'value' => $attributeFrontend->getValue($product)
            ];
        }

        return $data;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeValue
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function updateProductAttributeValue($product, $attributeValue): void
    {
        $product->setData('selling_features_bullets', $attributeValue);
        $this->productRepository->save($product);
    }
}
