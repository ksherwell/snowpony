<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Command\Product;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use BBApps\DataImporter\Command\AbstractImport;
use BBApps\DataImporter\Helper\Data;
use BBApps\DataImporter\Model\Csv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignProductLinks extends AbstractImport
{
    const SKUS_DELIMITER = ',';

    protected $commandName = 'assign-product-links';
    protected $commandDescription = 'Assign Products Links';
    protected $fileName = 'assign_product_links.csv';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    public function __construct(
        Csv $csv,
        State $state,
        ProductRepositoryInterface $productRepository,
        LinkFactory $linkFactory
    ) {
        parent::__construct($csv);

        // $state->setAreaCode(Area::AREA_ADMINHTML); // use 1 time in AssignAssociatedProduct?!
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
    }

    protected function isFileRequired()
    {
        return false;
    }

    protected function processRow($rowData, InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $sku = trim($rowData['sku']);
        unset($rowData['sku']);

        if ($sku) {
            $product = $this->getProductBySku($sku);
            if ($product) {
                $linkData = [];
                foreach ($rowData as $field => $value) {
                    if ($value) {
                        $linkSkus = explode(self::SKUS_DELIMITER, $value);
                        if (!empty($linkSkus)) {
                            foreach ($linkSkus as $linkSku) {
                                $linkSku = trim($linkSku);
                                $linkProduct = $this->getProductBySku($linkSku);
                                if ($linkProduct && $productLink = $this->createProductLink($sku, $linkSku, $field)) {
                                    $linkData[] = $productLink;
                                }
                            }
                        }
                    }
                }

                if (!empty($linkData)) {
                    try {
                        $product->setProductLinks($linkData);
                        $this->productRepository->save($product);
                        $output->writeln('Sku ' . $sku . ' Updated Successfully');
                        return true;
                    } catch (\Exception $e) {
                        $output->writeln('Error: ' . $e->getMessage() . ' Updated Un-successfully.');
                        return false;
                    }
                }
            }
        }

        $output->writeln('Sku ' . $sku . ' Updated Un-successfully.');
        return false;
    }

    /**
     * create product link
     *
     * @param $sku
     * @param $linkSku
     * @param $field
     * @return null|ProductLinkInterface
     */
    private function createProductLink($sku, $linkSku, $field)
    {
        $linkType = null;
        switch ($field) {
            case 'related_skus':
                $linkType = 'related';
                break;
            case 'crosssell_skus':
                $linkType = 'crosssell';
                break;
            case 'upsell_skus':
                $linkType = 'upsell';
                break;
        }

        if ($linkType) {
            $productLink = $this->linkFactory->create();
            return $productLink->setSku($sku)
                ->setLinkedProductSku($linkSku)
                ->setPosition(1)
                ->setLinkType($linkType);
        }

        return null;
    }

    private function getProductBySku($sku)
    {
        $product = null;
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            // do nothing
        }

        return $product;
    }
}
