<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Model\Warehouse\StoreFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Json\DecoderInterface;

class Dispatch extends \Magento\Framework\DataObject
{
    const STATUS_RESPONSE = 200;

    const DIRECTION_ORDER = 'order';

    const DIRECTION_QUOTE = 'quote';

    const DIRECTION_STORE = 'store';

    /**
     * @var array
     */
    protected $warehouses = [];

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    private $orderItem;

    /**
     * @var ResourceModel\Warehouse\Store\CollectionFactory
     */
    private $collectionStoreFactory;

    /**
     * @var Warehouse\ItemFactory
     */
    private $stockFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var ResourceModel\Warehouse\CustomerGroup\CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var \Magento\Framework\HTTP\ClientFactory
     */
    private $clientUrl;

    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    private $localeLists;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var array
     */
    protected $callables;

    /**
     * @var array
     */
    protected $exclude = [];

    /**
     * @var string
     */
    private $direction;

    /**
     * @var ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var ResourceModel\Warehouse\CollectionFactory
     */
    private $warehouseCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session\Proxy
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface\Proxy
     */
    private $storeManager;

    /**
     * @var int
     */
    protected $defaultId;

    /**
     * Dispatch constructor.
     *
     * @param ResourceModel\Warehouse\Store\CollectionFactory         $collectionStoreFactory
     * @param Warehouse\ItemFactory                                   $stockFactory
     * @param ResourceModel\Warehouse\Item\CollectionFactory          $stockCollectionFactory
     * @param StoreFactory                                            $storeFactory
     * @param WarehouseFactory                                        $whFactory
     * @param ResourceModel\Warehouse\CollectionFactory               $warehouseCollectionFactory
     * @param ResourceModel\Warehouse\CustomerGroup\CollectionFactory $groupCollectionFactory
     * @param \Amasty\MultiInventory\Helper\System                    $system
     * @param \Magento\Framework\HTTP\ClientFactory                   $clientUrl
     * @param RegionFactory                                           $regionFactory
     * @param \Magento\Framework\Locale\ListsInterface                $localeLists
     * @param DecoderInterface                                        $jsonDecoder
     * @param array                                                   $data
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store\CollectionFactory $collectionStoreFactory,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $stockFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $stockCollectionFactory,
        \Amasty\MultiInventory\Model\Warehouse\StoreFactory $storeFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup\CollectionFactory $groupCollectionFactory,
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\Framework\HTTP\ClientFactory $clientUrl,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Store\Model\StoreManagerInterface\Proxy $storeManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->collectionStoreFactory     = $collectionStoreFactory;
        $this->stockFactory               = $stockFactory;
        $this->stockCollectionFactory     = $stockCollectionFactory;
        $this->storeFactory               = $storeFactory;
        $this->warehouseFactory           = $whFactory;
        $this->groupCollectionFactory     = $groupCollectionFactory;
        $this->clientUrl                  = $clientUrl;
        $this->system                     = $system;
        $this->regionFactory              = $regionFactory;
        $this->localeLists                = $localeLists;
        $this->jsonDecoder                = $jsonDecoder;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->customerSession            = $customerSession;
        $this->storeManager               = $storeManager;
    }

    /**
     * Get Warehouse via Store View
     * @return $this
     */
    public function searchStoreView()
    {
        $store = $this->getStoreId();
        $stores = [$store, 0];

        $collection = $this->storeFactory->create()->getCollection()
            ->addFieldToFilter('store_id', ['in' => $stores]);
        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }
        if ($this->system->isLockOnStore() || $collection->getSize()) {
            $this->setFromCollection($collection);
        }

        return $this;
    }

    /**
     * Get the closest warehouse
     * @return $this
     */
    public function searchNearest()
    {
        $isCustomer = 0;
        $shipping = [];
        if ($this->getDirection() == self::DIRECTION_QUOTE) {
            $quote = $this->getQuoteItem()->getQuote();
            if ($quote->getCustomerId()) {
                $shipQuote = $quote->getCustomer()->getAddresses();
                $isCustomer = 1;
            } else {
                $shipQuote = $quote->getAddressesCollection();
            }
            if ($shipQuote) {
                foreach ($shipQuote as $addressItem) {
                    if ($isCustomer) {
                        $state = $addressItem->getRegion()->getRegion();
                    } else {
                        $state = $addressItem->getRegion();
                    }
                    $shipping = [
                        'country' => $this->localeLists->getCountryTranslation($addressItem->getCountryId()),
                        'state'   => $state,
                        'city'    => $addressItem->getCity(),
                        'address' => implode(",", $addressItem->getStreet()),
                        'zip'     => $addressItem->getPostCode()
                    ];
                }
            }
        }
        if ($this->getDirection() == self::DIRECTION_ORDER) {
            $shipOrder = $this->getOrderItem()->getOrder()->getAddresses();
            foreach ($shipOrder as $addressItem) {
                if ($addressItem->getAddressType() == 'shipping') {
                    $shipping = [
                        'country' => $this->localeLists->getCountryTranslation($addressItem->getCountryId()),
                        'state' => $addressItem->getRegion(),
                        'city' => $addressItem->getCity(),
                        'address' => implode(",", $addressItem->getStreet()),
                        'zip' => $addressItem->getPostCode()
                    ];
                }
            }
        }
        if ($shipping) {
            $collection = $this->warehouseCollectionFactory->create();
            if (count($this->warehouses)) {
                $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
            }
            $addresses = [];
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    if ($item->getCountry()) {
                        $addresses[$item->getId()] = [
                            'country' => $this->localeLists->getCountryTranslation($item->getCountry()),
                            'state' => $this->correctState($item->getState(), $item->getCountry()),
                            'city' => $item->getCity(),
                            'address' => $item->getAddress(),
                            'zip' => $item->getZip()
                        ];
                    }
                }
            }
            // We consider and determine the closest distance via Google Api
            if (count($addresses)) {
                $result = [];
                if ($this->system->isAddressSuggestionEnabled()) {
                    $destinations = '&destinations=' . $this->buildQuery($shipping);
                    foreach ($addresses as $key => $address) {
                        $origins = 'origins=' . $this->buildQuery($address);
                        $query = $origins . $destinations . "&key=" . $this->system->getGoogleMapsKey();
                        $clientUrl = $this->clientUrl->create();
                        $url = $this->system->getGoogleDistancematrix() . $query;
                        $clientUrl->get($url);
                        if ($clientUrl->getStatus() == self::STATUS_RESPONSE) {
                            $response = $this->jsonDecoder->decode($clientUrl->getBody());
                            if (count($response) && isset($response['rows'][0]['elements'][0])
                                && $response['rows'][0]['elements'][0]['status'] == 'OK'
                            ) {
                                $result[$key] = $response['rows'][0]['elements'][0]['distance']['value'];
                            }
                        }
                    }

                    if (count($result) > 0) {
                        asort($result);
                        reset($result);
                        $id = key($result);
                        $this->warehouses = [];
                        $this->warehouses[] = $id;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get Warehouse via Priority
     * @return $this
     */
    public function searchPriorityWarehouses()
    {
        $collection = $this->warehouseCollectionFactory->create();
        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        $collection->getSelect()->order('priority ASC');
        if ($collection->getSize()) {
            $this->warehouses = [];
            $priority = 0;
            $incPriority = 0;
            foreach ($collection as $collect) {
                if (!$collect->getPriority()) {
                    continue;
                }
                if (!$priority) {
                    $this->warehouses[] = $collect->getId();
                    $priority           = $collect->getPriority();
                    $incPriority++;
                    continue;
                }
                if ($incPriority == 1 && $priority == $collect->getPriority()) {
                    $this->warehouses[] = $collect->getId();
                } else {
                    $incPriority++;
                }
            }
        }

        return $this;
    }

    /**
     * Get Warehouse via Customer Group
     * @return $this
     */
    public function searchCustomerGroup()
    {
        $collection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('group_id', $this->getCustomerGroupId());
        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }
        if ($collection->getSize()) {
            $this->setFromCollection($collection);
        }

        return $this;
    }

    /**
     * Search warehose with whole stock for avoid split
     * @since 1.3.0
     */
    public function searchStock()
    {
        $qty = 'qty';
        if ($this->system->getAvailableDecreese()) {
            $qty = 'available_qty';
        }
        $collection = $this->stockCollectionFactory->create()
            ->addFieldToFilter($qty, ['gteq' => $this->getRequestedQty()])
            ->setOrder('available_qty');

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }
        if ($collection->getSize()) {
            $this->setFromCollection($collection);
        }
    }

    /**
     * Get Warehouse via Stock
     * @return $this
     */
    public function searchProductInStock()
    {
        $collection = $this->stockCollectionFactory->create()
            ->addFieldToFilter('product_id', $this->getProductId())
            ->addFieldToFilter(
                \Amasty\MultiInventory\Api\Data\WarehouseItemInterface::STOCK_STATUS,
                \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK
            )
            ->setOrder('available_qty');
        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }
        if ($collection->getSize()) {
            $this->warehouses = [];
            foreach ($collection as $collect) {
                $this->warehouses[] = $collect->getWarehouseId();
                break;
            }
        }
        return $this;
    }

    /**
     * Return Default Warehouse
     * @return $this
     */
    public function getGeneral()
    {
        $productId = $this->getProductId();
        /** @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\Collection $collection */
        $collection = $this->stockCollectionFactory->create()
            ->addActiveWarehouseFilter();

        if (!$this->system->isLockOnStore()) {
            $collection->addFieldToFilter(
                \Amasty\MultiInventory\Api\Data\WarehouseItemInterface::STOCK_STATUS,
                \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK
            );
        }

        $excludes = $this->getExludeWarehouses();
        if ($productId) {
            $collection->addFieldToFilter('product_id', $productId);
            if (isset($excludes[$productId]) && count($excludes[$productId])) {
                $excluded = [];
                foreach ($excludes[$productId] as $wh) {
                    if (!in_array($wh, $excluded)) {
                        $excluded[] = $wh;
                    }
                }
                if (!empty($excluded)) {
                    $collection->addFieldToFilter('main_table.warehouse_id', ['nin' => $excluded]);
                }
            }
        }
        $this->setFromCollection($collection);

        return $this;
    }

    /**
     * @return int[]
     * @since 1.3.0 added new criteria "Stock"
     */
    public function searchWh()
    {
        $this->warehouses = [];
        $this->getGeneral();
        if (count($this->warehouses) > 0) {
            $callables = $this->getCallables();
            foreach ($callables as $key => $options) {
                if ($this->checkCount()) {
                    return $this->warehouses;
                }

                if ($options['is_active']) {
                    switch ($key) {
                        case 'customer_group':
                            $this->searchCustomerGroup();
                            break;
                        case 'nearest':
                            $this->searchNearest();
                            break;
                        case 'priority_warehouses':
                            $this->searchPriorityWarehouses();
                            break;
                        case 'store_view':
                            $this->searchStoreView();
                            break;
                        case 'stock':
                            $this->searchStock();
                            break;
                        default:
                            $method = 'search' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
                            if (is_callable([$this, $method])) {
                                $this->{$method}();
                            }
                            break;
                    }
                }
            }
            if (count($this->warehouses) > 1) {
                $this->searchProductInStock();
            }
        }

        if (!count($this->warehouses) && $this->getDirection() !== self::DIRECTION_STORE) {
            $this->warehouses[] = $this->getDefaultWarehouseId();
        }

        return $this->warehouses;
    }

    /**
     * @return \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item
     */
    public function getObject()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem();
            default:
                return $this->_getData('object');
        }
    }

    /**
     * @return int|null
     */
    private function getProductId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getProductId();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getProduct()->getId();
            default:
                $data = $this->_getData('object');
                if (is_object($data) && $data->hasData('product_id')) {
                    return $data->getProductId();
                }
                return null;
        }
    }

    /**
     * @return int
     */
    private function getCustomerGroupId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getOrder()->getCustomerGroupId();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getQuote()->getCustomerGroupId();
            default:
                return $this->customerSession->getCustomerGroupId();
        }
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
            case self::DIRECTION_QUOTE:
                return $this->getObject()->getStoreId();
            default:
                return $this->storeManager->getStore()->getId();
        }
    }

    private function getRequestedQty()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getQtyOrdered();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getQty();
            default:
                return 1;
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getQuoteItem()
    {
        return $this->quoteItem;
    }

    /**
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     */
    public function setQuoteItem($item)
    {
        $this->quoteItem = $item;

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setOrderItem($item)
    {
        $this->orderItem = $item;

        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return bool
     */
    protected function checkCount()
    {
        if (count($this->warehouses) == 1 && !$this->system->isLockOnStore()) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    protected function setFromCollection($collection)
    {
        $this->warehouses = [];
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([\Amasty\MultiInventory\Api\Data\WarehouseInterface::ID])
            ->group(\Amasty\MultiInventory\Api\Data\WarehouseInterface::ID);

        foreach ($collection->getData() as $wh) {
            $this->warehouses[] = $wh[\Amasty\MultiInventory\Api\Data\WarehouseInterface::ID];
        }
    }

    /**
     * @return int
     */
    public function getWarehouse()
    {
        if (count($this->warehouses) >= 1) {
            return $this->warehouses[0];
        }

        return $this->getDefaultWarehouseId();
    }

    /**
     * @return int
     */
    public function getDefaultWarehouseId()
    {
        if ($this->defaultId === null) {
            $this->defaultId = $this->warehouseFactory->create()->getDefaultId();
        }

        return $this->defaultId;
    }

    /**
     * @return array
     */
    public function getWarehousesRaw()
    {
        return $this->warehouses;
    }

    /**
     * @param $address
     * @return string
     */
    private function buildQuery($address)
    {
        $query = "";
        $arrayCodes = ['country', 'state', 'city', 'address', 'zip'];
        foreach ($arrayCodes as $code) {
            if (isset($address[$code])) {
                if (strlen($query) > 0) {
                    $query .= " ";
                }
                $query .= $address[$code];
            }
        }

        return urlencode($query);
    }

    /**
     * @param array $callables
     * @return $this
     */
    public function setCallables($callables)
    {
        $this->callables = $callables;
        return $this;
    }

    /**
     * @return array
     */
    public function getCallables()
    {
        if ($this->callables === null) {
            $this->setCallables($this->system->getDispatchOrder());
        }
        return $this->callables;
    }

    /**
     * @return array
     */
    public function getExludeWarehouses()
    {
        return $this->exclude;
    }

    /**
     * Add Exclude Warehouse for product
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return $this
     */
    public function addExclude($productId, $warehouseId)
    {
        if (!isset($this->exclude[$productId])) {
            $this->exclude[$productId] = [];
        }
        $this->exclude[$productId][] = $warehouseId;

        return $this;
    }

    /**
     * reset array
     *
     * @return $this
     */
    public function resetExclude()
    {
        $this->exclude = [];

        return $this;
    }

    /**
     * @param $state
     * @param $countryId
     * @return string
     */
    private function correctState($state, $countryId)
    {
        if (!empty($state)) {
            if (is_numeric($state) && $countryId) {
                return $this->regionFactory->create()->loadByCode($state, $countryId)->getName();
            }
        }

        return $state;
    }
}
