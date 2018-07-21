<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\ImportExport\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use BBApps\DataImporter\Helper\Data as ImportHelper;

class AdditionalData extends AbstractModel
{
    const DEFAULT_ATTRIBUTE_SET = 'Default';
    const DEFAULT_ATTRIBUTE_GROUP = 'General';
    const DELIMITER_CATEGORY = '/';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ImportHelper
     */
    private $helper;

    /**
     * @var Product\Action
     */
    private $productAction;

    /**
     * @var \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax
     */
    protected $_attributeTax;

    /**
     * ProductImport constructor.
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param ProductRepositoryInterface $productRepository
     * @param ImportHelper $helper
     * @param array $data
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        ProductRepositoryInterface $productRepository,
        ImportHelper $helper,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax $attributeTax,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->_attributeTax     = $attributeTax;
        $this->productRepository = $productRepository;
        $this->helper            = $helper;
        $this->productAction     = $productAction;
    }

    public function import($data, $storeId)
    {
        //        $sku = str_replace('01-', '', $data['sku']);
        $sku = explode('-', $data['sku']);
        if (count($sku) == 4) {
            $sku = implode('-', [$sku[0], $sku[1], $sku[3]]);
            try {
                /** @var Product $product */
                $product = $this->productRepository->get($sku);
                $newData = $this->_prepareData($data);

                if ($product->getId()) {
                    $this->productAction->updateAttributes(
                        [$product->getId()],
                        $newData,
                        1
                    );
                }

                $this->_logger->critical($sku . ' - was updated');
            } catch (\Exception $e) {
                $this->_logger->critical($sku . ' - ' . $e->getMessage());
                //                $this->_logger->error($e->getMessage());
                //                return false;
            }
        } else {
            $this->_logger->critical($data['sku'] . ' does not exist');
        }
        return true;
    }

    private function _prepareData($data)
    {
        unset($data['sku']);
        unset($data['item_summary']);
        unset($data['item_info_title']);
        return $data;
    }
}
