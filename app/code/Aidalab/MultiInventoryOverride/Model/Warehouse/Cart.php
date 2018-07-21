<?php


namespace Aidalab\MultiInventoryOverride\Model\Warehouse;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\DecoderInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session;

class Cart extends \Amasty\MultiInventory\Model\Warehouse\Cart{


    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory
     */
    private $quoteItemWhFactory;
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory
     */
    private $quoteItemWhCollection;
    /**
     * @var \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface
     */
    private $quoteItemWhRepository;

    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $itemCollection,
        \Amasty\MultiInventory\Model\Warehouse\ItemRepository $stockRepository,
        \Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory $quoteItemWhFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory $quoteItemWhCollection,
        \Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface $quoteItemWhRepository,
        \Amasty\MultiInventory\Helper\System $system,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Amasty\MultiInventory\Model\Dispatch $dispatch,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator $quantityValidator,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote\Proxy $backendQuote,
        array $data = [])
    {
        parent::__construct($itemCollection, $stockRepository, $quoteItemWhFactory, $quoteItemWhCollection, $quoteItemWhRepository, $system, $checkoutCart, $dispatch, $itemProcessor, $eventManager, $quantityValidator, $appState, $backendQuote, $data);
        $this->quoteItemWhFactory = $quoteItemWhFactory;
        $this->quoteItemWhCollection = $quoteItemWhCollection;
        $this->quoteItemWhRepository = $quoteItemWhRepository;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     */
    public function addWhToQuote($quoteItem)
    {
        if ($result = $this->dispatchWarehouse($quoteItem)) {
            foreach ($result as $item) {
                $quoteWhItem = $this->quoteItemWhFactory->create();
                $quoteWhItem->addData($item->getData());
                $this->saveQuoteItemWarehouse($quoteWhItem);
            }
        }
    }

    /**
     * @param \Amasty\MultiInventory\Model\Warehouse\Quote\Item $quoteWhItemToSave
     */
    private function saveQuoteItemWarehouse($quoteWhItemToSave)
    {
        /** @var \Amasty\MultiInventory\Model\Warehouse\Quote\Item $quoteWhItem */
        $quoteWhItem = $this->quoteItemWhCollection->create()
            ->addFieldToFilter('quote_item_id', $quoteWhItemToSave->getQuoteItemId())
//Excepted create many unusable quote Items in `amasty_multiinventory_warehouse quote_item` table:
            //->addFieldToFilter('warehouse_id', $quoteWhItemToSave->getWarehouseId())
            ->setPageSize(1)
            ->getFirstItem();

        if (!$quoteWhItem->isObjectNew()) {
            $quoteWhItemToSave->setId($quoteWhItem->getId());
        }

        try {
            $this->quoteItemWhRepository->save($quoteWhItemToSave);
        } catch (LocalizedException $e) {
        }
    }
}