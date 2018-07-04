<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Magento\Backend\App\Action\Context;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\DecoderInterface;

class Save extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var Warehouse\CustomerGroupFactory
     */
    private $groupFactory;

    /**
     * @var Warehouse\StoreFactory
     */
    private $storeFactory;

    /**
     * @var Warehouse\ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $factory;

    /**
     * @var Warehouse\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     * @param Warehouse\CustomerGroupFactory $groupFactory
     * @param Warehouse\StoreFactory $storeFactory
     * @param Warehouse\ShippingFactory $shippingFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $factory
     * @param Warehouse\ItemFactory $itemFactory
     * @param \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Amasty\MultiInventory\Model\Warehouse\CustomerGroupFactory $groupFactory,
        \Amasty\MultiInventory\Model\Warehouse\StoreFactory $storeFactory,
        \Amasty\MultiInventory\Model\Warehouse\ShippingFactory $shippingFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $itemFactory,
        \Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface $itemRepository,
        DecoderInterface $jsonDecoder
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->shippingFactory = $shippingFactory;
        $this->factory = $factory;
        $this->itemFactory = $itemFactory;
        $this->itemRepository = $itemRepository;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $extData = [];
            $id = $this->getRequest()->getParam('warehouse_id');
            if (empty($data['warehouse_id'])) {
                $data['warehouse_id'] = null;
            }
            if (!$id) {
                $model = $this->factory->create();
                if (isset($data['code'])) {
                    $collection = $this->factory->create()->getCollection()->addFieldToFilter('code', $data['code']);
                    if ($collection->getSize()) {
                        $this->messageManager->addErrorMessage(__('This warehouse code already exists.'));
                        return $resultRedirect->setPath('*/*/new');
                    }
                }
            } else {
                $model = $this->repository->getById($id);
                if (!$model->getId() && $id) {
                    $this->messageManager->addErrorMessage(__('This warehouse no  longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            list($data, $extData) = $this->unScopeData($data);

            $model->setData($data);
            try {
                if (!$id) {
                    $this->repository->save($model);
                }
                $this->setExtData($model, $extData);
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the warehouse.'));
                $this->dataPersistor->clear('amasty_multi_inventory_warehouse');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the warehouse.'));
            }

            $this->dataPersistor->set('amasty_multi_inventory_warehouse', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['warehouse_id' => $this->getRequest()->getParam('warehouse_id')]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $data
     * @return array
     */
    private function unScopeData($data)
    {
        $extData = [];
        if (!$this->getRequest()->getParam('warehouse_id', 0)) {
            if (isset($data['order_email_notification']) && !$data['order_email_notification']) {
                $data['order_email_notification'] = $data['email'];
            }
            if (isset($data['low_stock_notification']) && !$data['low_stock_notification']) {
                $data['low_stock_notification'] = $data['email'];
            }
        }
        if (isset($data['state_id']) && $data['state_id']) {
            $data['state'] = $data['state_id'];
            unset($data['state_id']);
        }
        if (isset($data['storeviews'])) {
            $extData['storeviews'] = $data['storeviews'];
            unset($data['storeviews']);
        }
        if (isset($data['customer_groups'])) {
            $extData['customer_groups'] = $data['customer_groups'];
            unset($data['customer_groups']);
        }
        if (isset($data['warehouse_products'])) {
            $extData['warehouse_products'] = $data['warehouse_products'];
            unset($data['warehouse_products']);
        }
        if (isset($data['shippings'])) {
            $extData['shippings'] = $data['shippings'];
            unset($data['shippings']);
        }

        return [$data, $extData];
    }

    /**
     * @param $model
     * @param $data
     */
    private function setExtData(
        $model,
        $data
    ) {
        if (isset($data['storeviews'])) {
            $ids = $model->getStoreIds();
            $newRows = array_diff($data['storeviews'], $ids);
            $delRows = array_diff($ids, $data['storeviews']);
            if (count($data['storeviews']) > 1 && in_array(0, $data['storeviews'])) {
                $delRows[] = 0;
            }
            if (count($delRows)) {
                foreach ($delRows as $id) {
                    $model->deleteStore($id);
                }
            }
            if (count($newRows)) {
                foreach ($newRows as $id) {
                    $object = $this->storeFactory->create();
                    $object->setStoreId($id);
                    $model->addStore($object);
                }
            }
        }

        if (isset($data['customer_groups'])) {
            $ids = $model->getGroupIds();
            $newRows = array_diff($data['customer_groups'], $ids);
            $delRows = array_diff($ids, $data['customer_groups']);
            if (count($delRows)) {
                foreach ($delRows as $id) {
                    $model->deleteGroup($id);
                }
            }
            if (count($newRows)) {
                foreach ($newRows as $id) {
                    $object = $this->groupFactory->create();
                    $object->setGroupId($id);
                    $model->addGroupCustomer($object);
                }
            }
        }
        if (isset($data['warehouse_products'])) {
            $data['warehouse_products'] = $this->jsonDecoder->decode($data['warehouse_products']);
            if (count($data['warehouse_products'])) {
                $ids = $model->getItemIds();
                $delRows = array_diff($ids, array_keys($data['warehouse_products']));
                if (count($delRows)) {
                    foreach ($delRows as $id) {
                        $collection = $this->itemFactory->create()->getCollection();
                        $collection->addFieldToFilter('warehouse_id', $model->getId())
                            ->addFieldToFilter('product_id', $id);
                        if ($collection->getSize()) {
                            $delItem = $collection->getFirstItem();
                            $model->addRemoveItem($delItem);
                            $model->deleteItems($id);
                        }
                    }
                }
                foreach ($data['warehouse_products'] as $id => $products) {
                    $object = $this->itemFactory->create();
                    $object->addData($products);
                    $object->setProductId($id);
                    $model->addItem($object);
                }
            }
        }

        if (isset($data['shippings'])) {
            if (!empty($data['shippings'])) {
                $shippings = [];
                foreach ($data['shippings'] as $code => $rate) {
                    if (!empty($rate)) {
                        $shippings[$code] = $rate;
                    }
                }
                $codes = $model->getShippingsCodes();
                $newRows = array_keys($data['shippings']);
                $delRows = $codes;
                if (count($delRows)) {
                    foreach ($delRows as $code) {
                        $model->deleteShipping($code);
                    }
                }

                if (count($newRows)) {
                    foreach ($newRows as $code) {
                        if (isset($shippings[$code])) {
                            $object = $this->shippingFactory->create();
                            $object->setShippingMethod($code);
                            $object->setRate($shippings[$code]);

                            $model->addShippings($object);
                        }
                    }
                }
            }
        }
    }
}
