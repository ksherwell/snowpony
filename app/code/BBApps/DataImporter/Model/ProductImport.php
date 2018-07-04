<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\ImportExport\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use BBApps\DataImporter\Helper\Data as ImportHelper;

class ProductImport extends AbstractModel
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
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->productRepository = $productRepository;
        $this->helper            = $helper;
        $this->productAction     = $productAction;
    }

    public function import($data, $storeId)
    {

        try {
            /** @var Product $product */
            $product = $this->productRepository->get($data['sku']);
            $data    = $this->_prepareData($data);

            if ($product->getId()) {
                $this->productAction->updateAttributes(
                    [$product->getId()],
                    $data,
                    $storeId
                );
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

        /* remove sku from data */
        if (! empty($data['sku'])) {
            unset($data['sku']);
        }

        if (! empty($data['price_ttc'])) {
            $data['special_price'] = $data['price_ttc'];
            unset($data['price_ttc']);
        }

        if (! empty($data['strike_price'])) {
            $data['price'] = $data['strike_price'];
            unset($data['strike_price']);
        }

        return $data;
    }
}
