<?php

namespace BBApps\DataImporter\Model\ResourceModel\Attribute\Backend\Weee;

/**
 * Class Tax
 * @package BBApps\DataImporter\Model\ResourceModel\Attribute\Backend\Weee
 */
class Tax extends \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax
{
    /**
     * Load product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     */
    public function loadProductData($product, $attribute)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['website_id', 'country', 'state', 'value']
        )->where(
            'entity_id = ?',
            (int) $product->getId()
        )->where(
            'attribute_id = ?',
            (int) $attribute->getId()
        );
        //        if ($attribute->isScopeGlobal()) {
        //            $select->where('website_id = ?', 0);
        //        } else {
        $storeId = $product->getStoreId();
        if ($storeId) {
            $select->where(
                'website_id IN (?)',
                [0, $this->_storeManager->getStore($storeId)->getWebsiteId()]
            );
            //            }
        }
        return $this->getConnection()->fetchAll($select);
    }
}
