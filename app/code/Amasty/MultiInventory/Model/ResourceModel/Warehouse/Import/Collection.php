<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    const TABLE_PRODUCT = 'catalog_product_entity';

    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param AttributeFactory $attributeFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->typeFactory = $typeFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Amasty\MultiInventory\Model\Warehouse\Import',
            'Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import'
        );
    }

    /**
     * @return $this
     */
    public function joinProducts()
    {
        $this->getSelect()
            ->joinLeft(
                ['cpe' => $this->getTable(self::TABLE_PRODUCT)],
                'cpe.entity_id = main_table.product_id',
                ['sku' => 'cpe.sku']
            );
        if ($attr = $this->getAttributeCode('name')) {
            $idFieldName = $this->getConnection()->getAutoIncrementField($this->getTable('catalog_product_entity'));
            $this->getSelect()
                ->joinLeft(
                    ['ancpe' => $this->getTable(sprintf('catalog_product_entity_%s', $attr->getBackendType()))],
                    sprintf('ancpe.%s = main_table.product_id', $idFieldName),
                    ['name' => 'ancpe.value']
                );
            $this->getSelect()->where(sprintf('ancpe.attribute_id = %s', $attr->getAttributeId()));
            $this->getSelect()->where(sprintf('ancpe.store_id = %s', 0));
        }
       
        return $this;
    }

    /**
     * @param $name
     * @return \Magento\Framework\DataObject|null
     */
    public function getAttributeCode($name)
    {
        if ($typeId = $this->getEntityType()) {
            $collection = $this->attributeFactory->create()->getCollection()
                ->addFieldToFilter('attribute_code', $name)
                ->addFieldToFilter('entity_type_id', $typeId);
            if ($collection->getSize() > 0) {
                return $collection->getFirstItem();
            }
        }

        return null;
    }

    /**
     * @return null
     */
    public function getEntityType()
    {
        $collection = $this->typeFactory->create()->getCollection()->addFieldToFilter(
            'entity_table',
            self::TABLE_PRODUCT
        );
        if ($collection->getSize() > 0) {
            return $collection->getFirstItem()->getEntityTypeId();
        }

        return null;
    }
}
