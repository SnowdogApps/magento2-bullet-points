<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

class AttributeProcessor extends AbstractProcessor
{
    /**
     * @param array $attributeIds
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAttributesData($attributeIds): array
    {
        $attributesData = [];
        $productEntityTypeId = $this->getEntityTypeId('catalog_product');

        foreach ($attributeIds as $attributeId) {
            $select = $this->getConnection()->select()->from(
                $this->getResourceConnection()->getTableName('eav_attribute'),
                ['attribute_id', 'attribute_code', 'frontend_label', 'backend_type']
            )->where(
                'attribute_id = ?',
                $attributeId
            )->where(
                'entity_type_id = ?',
                $productEntityTypeId
            );
            $result = $this->getConnection()->query($select)->fetch();

            if (!empty($result)) {
                $attributesData[$result['attribute_code']] = $result;
            }
        }

        return $attributesData;
    }
}
