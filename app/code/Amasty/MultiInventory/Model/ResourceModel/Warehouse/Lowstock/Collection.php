<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock;

class Collection extends \Magento\Reports\Model\ResourceModel\Product\Collection
{

    private $inventoryItemJoined = false;

    /**
     * @var string
     */
    private $inventoryItemTableAlias = 'amasty_lowstock_inventory_item';

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item
     */
    private $itemResource;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $helper;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $itemResource
     * @param \Amasty\MultiInventory\Helper\System $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $itemResource,
        \Amasty\MultiInventory\Helper\System $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $product,
            $eventTypeFactory,
            $productType,
            $quoteResource,
            $connection
        );
        $this->itemResource = $itemResource;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    private function getInventoryItemTable()
    {
        return $this->itemResource->getMainTable();
    }

    /**
     * @return string
     */
    private function getInventoryItemTableAlias()
    {
        return $this->inventoryItemTableAlias;
    }

    /**
     * @param $field
     * @param null $alias
     * @return $this
     */
    private function addInventoryItemFieldToSelect($field, $alias = null)
    {
        if (empty($alias)) {
            $alias = $field;
        }

        if (isset($this->_joinFields[$alias])) {
            return $this;
        }

        $this->_joinFields[$alias] = ['table' => $this->getInventoryItemTableAlias(), 'field' => $field];

        $this->getSelect()->columns([$alias => $field], $this->getInventoryItemTableAlias());

        return $this;
    }

    /**
     * @param $field
     * @return string
     */
    private function getInventoryItemField($field)
    {
        return sprintf('%s.%s', $this->getInventoryItemTableAlias(), $field);
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function joinInventoryItem($fields = [])
    {
        if (!$this->inventoryItemJoined) {
            $this->getSelect()->joinLeft(
                [$this->getInventoryItemTableAlias() => $this->getInventoryItemTable()],
                sprintf(
                    'e.%s = %s.product_id',
                    $this->getEntity()->getEntityIdField(),
                    $this->getInventoryItemTableAlias()
                ),
                []
            );
            $this->inventoryItemJoined = true;
        }

        if (!is_array($fields)) {
            if (empty($fields)) {
                $fields = [];
            } else {
                $fields = [$fields];
            }
        }

        foreach ($fields as $alias => $field) {
            if (!is_string($alias)) {
                $alias = null;
            }
            $this->addInventoryItemFieldToSelect($field, $alias);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function useNotifyStockQtyFilter()
    {
        $this->joinInventoryItem(['qty']);
        $lowStock = $this->helper->getLowStock();
        $this->getSelect()->where($this->getInventoryItemField('qty') . ' <= ?', $lowStock);

        return $this;
    }

    /**
     * @return $this
     */
    public function setSimpleType()
    {
        $this->addAttributeToFilter('type_id', 'simple');
        
        return $this;
    }

    /**
     * @param $items
     * @return $this
     */
    public function setWarehouses($items)
    {
        if ($items) {
            $this->getSelect()->where($this->getInventoryItemField('warehouse_id') . ' IN(?)', $items);
        }
        return $this;
    }
}
