<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Export;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'export_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Amasty\MultiInventory\Model\Export',
            'Amasty\MultiInventory\Model\ResourceModel\Export'
        );
    }
}
