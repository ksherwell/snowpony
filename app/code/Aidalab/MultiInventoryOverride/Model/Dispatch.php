<?php

namespace Aidalab\MultiInventoryOverride\Model;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Json\DecoderInterface;

class Dispatch extends \Amasty\MultiInventory\Model\Dispatch
{
    protected $warehouses = [];
    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Magento\Customer\Model\Session\Proxy
     */
    private $customerSession;
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse
     */
    private $warehouseModel;

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
        \Magento\Framework\Locale\ListsInterface $localeLists, DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Store\Model\StoreManagerInterface\Proxy $storeManager,
        WarehouseRepositoryInterface $repository,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Amasty\MultiInventory\Model\Warehouse $warehouseModel,

        array $data = []
    )
    {
        $this->repository = $repository;
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;

        parent::__construct(
            $collectionStoreFactory,
            $stockFactory,
            $stockCollectionFactory,
            $storeFactory,
            $whFactory,
            $warehouseCollectionFactory,
            $groupCollectionFactory,
            $system,
            $clientUrl,
            $regionFactory,
            $localeLists,
            $jsonDecoder,
            $customerSession,
            $storeManager,
            $data
        );

        $this->warehouseModel = $warehouseModel;
    }

    public function searchByPostCode()
    {
        $customerZip = null;
        if ($this->getDirection() == self::DIRECTION_QUOTE) {
            $customerZip = $this->getQuoteItem()->getQuote()->getShippingAddress()->getPostcode();
        }
        if ($this->getDirection() == self::DIRECTION_ORDER) {
            $customerZip = $this->getOrderItem()->getOrder()->getShippingAddress()->getPostcode();
        }

        if ($customerZip) {
            $warehousesFilterByPostCode = [];
            foreach ($this->warehouses as $warehouse) {
                try {
                    $warehouseZips = $this->repository->getById($warehouse)->getWhDeliveryPostcodes();
                } catch (\Exception $ex) {
                    break;
                }

                $warehouseZips = str_replace(' ', '',  $warehouseZips);
                $warehouseZips = explode(',', $warehouseZips);
                if (in_array($customerZip, $warehouseZips)) {
                    $warehousesFilterByPostCode[] = $warehouse;
                }
            }
            $this->warehouses = $warehousesFilterByPostCode;
        }
    }

    /**
     * @param null $customerZip
     * @return int[]
     * @since 1.3.0 added new criteria "Stock"
     */
    public function searchWh()
    {
        $this->warehouses = [];
        $this->getGeneral();
        if (count($this->warehouses) > 0) {
            $this->searchByPostCode();

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
}
