<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Import extends AbstractDb
{
    const AMASTY_INVENTORY_IMPORT = 'amasty_multiinventory_warehouse_import';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $factory;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_multiinventory_warehouse_import', 'item_id');
    }

    /**
     * Item constructor.
     * @param Context $context
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $factory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function getMaxNumber()
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_IMPORT)],
            ['max' => new \Zend_Db_Expr(sprintf('MAX(%s)', 'import_number'))]
        );
        $bind = [];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return mixed
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
