<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Import\Edit;

class Before extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\MultiInventory\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ImportFactory
     */
    private $importFactory;

    /**
     * Before constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Amasty\MultiInventory\Helper\Data $helper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Amasty\MultiInventory\Model\Warehouse\ImportFactory $importFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\MultiInventory\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Amasty\MultiInventory\Model\Warehouse\ImportFactory $importFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->whFactory = $whFactory;
        $this->productFactory = $productFactory;
        $this->importFactory = $importFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getUrlUploader()
    {
        return $this->getUrl('amasty_multi_inventory/*/uploader');
    }

    /**
     * @return float
     */
    public function getMaxSize()
    {
        return $this->helper->getMaxSizeFile();
    }

    /**
     * @return string
     */
    public function listIdentifier()
    {
        return $this->jsonEncoder->encode([0 => 'sku', 1 => 'id']);
    }

    /**
     * @return string
     */
    public function listFileType()
    {
        return $this->jsonEncoder->encode([0 => 'csv', 1 => 'xml', 2 => 'xml']);
    }

    /**
     * @return string
     */
    public function listCodes()
    {
        return $this->jsonEncoder->encode($this->whFactory->create()->getWhCodes());
    }

    /**
     * @return string
     */
    public function listProducts()
    {
        $list = [];
        $ids = $this->productFactory->create()->getCollection()
            ->addFieldToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->getAllIds();
        $data = $this->productFactory->create()->getResource()->getProductsSku($ids);
        foreach ($data as $row) {
            $list[$row['sku']] = $row['entity_id'];
        }

        return $this->jsonEncoder->encode($list);
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/send');
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/clear');
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/grid');
    }

    /**
     * @return string
     */
    public function getDeleteFileUrl()
    {
        return $this->getUrl('amasty_multi_inventory/*/deletefile');
    }
}
