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

class ProductStockImport extends AbstractModel
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
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

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
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->productRepository = $productRepository;
        $this->helper            = $helper;
        $this->productAction     = $productAction;
        $this->stockRegistry     = $stockRegistry;
    }

    public function import($data, $storeId)
    {

        try {
            /** @var Product $product */
            $product = $this->productRepository->get($data['sku']);
            $data    = $this->_prepareData($data);

            if ($product->getId()) {
                $stockItem = $this->stockRegistry->getStockItemBySku($data['sku']);
                $stockItem->setQty($data['qty']);

                $this->stockRegistry->updateStockItemBySku($data['sku'], $stockItem);
                unset($data['qty']);

                /* remove sku from data */
                if (! empty($data['sku'])) {
                    unset($data['sku']);
                }

                $product->setStatus($data['status']);
                $product->setPrice($data['price']);
                $product = $this->productRepository->save($product);
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function _prepareData($data)
    {
        /* remove code client */
        if (! empty($data['code_client'])) {
            unset($data['code_client']);
        }

        if (! empty($data['stock'])) {
            $data['qty'] = $data['stock'];
            unset($data['stock']);
        }

        if ($data['status'] == 0) {
            $data['status'] = 2;
        }

        return $data;
    }
}
