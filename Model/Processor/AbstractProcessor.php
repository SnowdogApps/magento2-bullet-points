<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Model\Processor;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

abstract class AbstractProcessor
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return ResourceConnection
     */
    protected function getResourceConnection(): ResourceConnection
    {
        return $this->resourceConnection;
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        if (!$this->connection) {
            $this->connection = $this->getResourceConnection()->getConnection();
        }

        return $this->connection;
    }

    /**
     * @param string $entityTypeCode
     * @return int
     */
    public function getEntityTypeId($entityTypeCode): int
    {
        $select = $this->getConnection()->select()->from(
            $this->getResourceConnection()->getTableName('eav_entity_type'),
            ['entity_type_id']
        )->where(
            'entity_type_code = ?',
            $entityTypeCode
        );
        $result = $this->getConnection()->query($select);

        return (int) $result->fetchColumn();
    }
}
