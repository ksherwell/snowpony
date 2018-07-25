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
        $sku = str_replace('01-', '', $data['sku']);
        $sku = explode('-', $sku);
        if (count($sku) == 4) {
            $sku = implode('-', [$sku[0], $sku[1], $sku[3]]);
            try {
                /** @var Product $product */
                $product = $this->productRepository->get($sku);
                $newData = $this->_prepareData($data);

                if ($product->getId()) {

                    $children = $product->getTypeInstance()->getUsedProducts($product);
                    if (! empty($children)) {
                        foreach ($children as $child) {
                            $attributeModel = \Magento\Framework\App\ObjectManager::getInstance()->get
                            (\Magento\Catalog\Model\ResourceModel\Product\Action::class);
                            $attribute      = $attributeModel->getAttribute('fpt');

                            $this->_attributeTax->deleteProductData($product, $attribute);

                            $newData['attribute_id'] = $attribute->getId();
                            $newData['value']        = round($data['rate'] * $child->getPrice() / 100, 2);
                            $this->_attributeTax->insertProductData($product, $newData);
                            break;
                        }
                    }


                    //                    }
                    //                    $this->productAction->updateAttributes(
                    //                        [$product->getId()],
                    //                        $data,
                    //                        1
                    //                    );
                }
            } catch (\Exception $e) {
                $this->_logger->critical($sku . '-' . $e->getMessage());
                //                $this->_logger->error($e->getMessage());
                //                return false;
            }
        }
        return true;
    }

    private function _prepareData($data)
    {

        return [
            "website_id" => '1',
            "country"    => "AU",
            "state"      => "", // optional
            "value"      => 0
        ];
    }
}
