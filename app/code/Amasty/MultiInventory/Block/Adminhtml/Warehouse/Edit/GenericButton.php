<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Edit;

use Magento\Backend\Block\Widget\Context;
use Amasty\MultiInventory\Model\WarehouseFactory as Factory;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Factory
     */
    protected $warehouseFactory;

    /**
     * @var \Amasty\MultiInventory\Api\WarehouseRepositoryInterface
     */
    public $repository;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param Factory $warehouseFactory
     * @param \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        Factory $warehouseFactory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository
    ) {
        $this->context = $context;
        $this->warehouseFactory = $warehouseFactory;
        $this->repository = $repository;
    }


    public function getWarehouse()
    {
        if ($this->context->getRequest()->getParam('warehouse_id')) {
            try {
                return $this->repository->getById($this->context->getRequest()->getParam('warehouse_id'));
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t get warehouse'), $e);
            }
        }

        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
