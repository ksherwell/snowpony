<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace BBApps\Catalog\Block\Product\ProductList;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Catalog product related items block
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = [],
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $checkoutCart, $catalogProductVisibility, $checkoutSession, $moduleManager,
            $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */

        $categoryId = $product->getCategoryId();
        if (! $categoryId) {
            $categoryIds = $product->getCategoryIds();
            if ($categoryIds) {
                $categoryId = end($categoryIds);
            }
        }
        if ($categoryId) {
            $this->_itemCollection = $this->_categoryFactory->create()->load($categoryId)
                ->getProductCollection()->addAttributeToSelect(
                    'required_options'
                );

            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_itemCollection);
            }
            $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
            $this->_itemCollection->setPage(1, 10);

            $this->_itemCollection->load();

            foreach ($this->_itemCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        } else {
            $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
                'required_options'
            )->setPositionOrder()->addStoreFilter();

            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_itemCollection);
            }
            $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

            $this->_itemCollection->load();

            foreach ($this->_itemCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        }

        return $this;
    }
}
