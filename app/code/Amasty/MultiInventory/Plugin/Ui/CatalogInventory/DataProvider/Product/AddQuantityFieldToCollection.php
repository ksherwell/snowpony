<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Ui\CatalogInventory\DataProvider\Product;

class AddQuantityFieldToCollection
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * AddQuantityFieldToCollection constructor.
     * @param \Amasty\MultiInventory\Helper\System $system
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     */
    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Magento\Framework\App\ResourceConnection $connection
    ) {
        $this->system = $system;
        $this->whFactory = $whFactory;
        $this->connection = $connection;
    }

    /**
     * @param \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object
     * @param \Closure $work
     * @param \Magento\Framework\Data\Collection $collection
     * @param $field
     * @param null $alias
     */
    public function aroundAddField(
        \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object,
        \Closure $work,
        \Magento\Framework\Data\Collection $collection,
        $field,
        $alias = null
    ) {
        if ($this->system->isMultiEnabled()) {
            $collection->joinField(
                'qty',
                $this->connection->getTableName('amasty_multiinventory_warehouse_item'),
                'qty',
                'product_id=entity_id',
                sprintf('{{table}}.warehouse_id=%s', $this->whFactory->create()->getDefaultId()),
                'left'
            );
        } else {
            $work($collection, $field, $alias);
        }
    }
}
