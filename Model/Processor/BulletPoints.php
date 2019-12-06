<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

class BulletPoints
{
    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * @var AttributeProcessor
     */
    private $attributeProcessor;

    public function __construct(
        CategoryProcessor $categoryProcessor,
        ProductProcessor $productProcessor,
        AttributeProcessor $attributeProcessor
    ) {
        $this->categoryProcessor = $categoryProcessor;
        $this->productProcessor = $productProcessor;
        $this->attributeProcessor = $attributeProcessor;
    }

    /**
     * @param array $attributeIds
     * @param array $categoryIds
     * @param array $skus
     * @return void
     */
    public function execute($attributeIds, $categoryIds = [], $skus = []): void
    {
        $categoryIds = $this->categoryProcessor->getCategories($categoryIds);
        $productIdsArray = $this->productProcessor->getProductsIds($categoryIds, $skus);
        $productIds = $productIdsArray['product_ids'];
        $attributes = $this->attributeProcessor->getAttributesData($attributeIds);
        $productAttributesData = $this->productProcessor->getProductAttributesData($productIds, $attributes);
        $productAttributesDataWithHtmlList = $this->getDataWithHtmlList($productAttributesData, $attributes);

        $this->updateProductAttributeValue($productAttributesDataWithHtmlList);
    }

    /**
     * @param array $productAttributesData
     * @return void
     */
    private function updateProductAttributeValue($productAttributesData): void
    {
        if (!empty($productAttributesData)) {
            foreach ($productAttributesData as $data) {
                $this->productProcessor->updateProductAttributeValue($data['product_id'], $data['html']);
            }
        }
    }

    /**
     * @param array $productAttributesData
     * @param array $attributes
     * @return array
     */
    private function getDataWithHtmlList($productAttributesData, $attributes): array
    {
        if (empty($productAttributesData)) {
            return $productAttributesData;
        }

        foreach ($productAttributesData as $key => $attributesData) {
            $data = [];
            foreach ($attributes as $attribute) {
                if (!isset($attributesData[$attribute['attribute_code']])
                    || is_null($attributesData[$attribute['attribute_code']])
                ) {
                    continue;
                }

                $data[] = [
                    'label' => $attribute['frontend_label'],
                    'value' => $attributesData[$attribute['attribute_code']],
                ];
            }

            $attributesData['html'] = $this->generateHtmlList($data);
            $productAttributesData[$key] = $attributesData;
        }

        return $productAttributesData;
    }

    /**
     * @param array $data
     * @return string
     */
    private function generateHtmlList($data): string
    {
        $html = '<dl>';
        foreach ($data as $value) {
            $html .= '<dt>' . $value['label'] . '</dt><dd>' . $value['value'] . '</dd>';
        }
        $html .= '</dl>';

        return $html;
    }
}
