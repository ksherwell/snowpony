<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\MassAction;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\Collection\AbstractDb;

class Filter extends \Magento\Ui\Component\MassAction\Filter
{

    const NUMBER_PARAM = 'import_number';

    protected function applySelection(AbstractDb $collection)
    {
        $importNumber = $this->getImportNumber();
        if ($importNumber) {
            $collection->addFieldToFilter(static::NUMBER_PARAM, $importNumber);
        }

        return parent::applySelection($collection);
    }

    public function getImportNumber()
    {
        return $this->request->getParam(static::NUMBER_PARAM, null);
    }
}
