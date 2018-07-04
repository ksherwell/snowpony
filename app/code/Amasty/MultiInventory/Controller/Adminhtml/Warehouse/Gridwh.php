<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

class Gridwh extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Gridwh constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $repository
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $repository,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->repository = $repository;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id', false);
        if ($productId) {
            $product = $this->repository->getById($productId);
            $this->coreRegistry->register('current_product', $product);
        }
        if (!$product) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('warehouse/*/', ['_current' => true, 'id' => null]);
        }
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Warehouse',
                'warehouse.grid'
            )->toHtml()
        );
    }
}
