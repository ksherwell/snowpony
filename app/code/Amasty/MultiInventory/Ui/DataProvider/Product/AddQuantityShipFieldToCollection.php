<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

class AddQuantityShipFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * ProductDataProvider constructor.
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     */
    public function __construct(
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Magento\Framework\App\ResourceConnection $connection
    ) {
        $this->whFactory = $whFactory;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'ship_qty',
            $this->connection->getTableName('amasty_multiinventory_warehouse_item'),
            'ship_qty',
            'product_id=entity_id',
            sprintf('{{table}}.warehouse_id=%s', $this->whFactory->create()->getDefaultId()),
            'left'
        );
    }
}
