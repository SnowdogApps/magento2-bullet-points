<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

class CategoryProcessor extends AbstractProcessor
{
    /**
     * @param array $categoryIds
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getCategories($categoryIds = []): array
    {
        $result = [];
        $select = $this->getConnection()->select()->from(
            ['cce' => $this->getResourceConnection()->getTableName('catalog_category_entity')],
            ['entity_id']
        );
        
        if (!empty($categoryIds)) {
            $select->where('cce.entity_id IN (?)', $categoryIds);
        }

        $rows = $this->getConnection()->query($select)->fetchAll();
        foreach ($rows as $row) {
            $result[] = $row['entity_id'];
        }

        return $result;
    }
}
