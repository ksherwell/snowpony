<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Config\Source;

class Backorders extends \Magento\CatalogInventory\Model\Source\Backorders
{
    use \Amasty\MultiInventory\Traits\ConfigOptions;

    const USE_CONFIG_OPTION_VALUE = '-1';
}
