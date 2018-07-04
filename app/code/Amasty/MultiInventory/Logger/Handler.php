<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Logger;

use Magento\Framework\Filesystem\DriverInterface;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        $filePath = null
    ) {

        $date = $timezone->date();
        $this->fileName = '/var/log/Amasty-inventory-' . $date->format('y-m-d') . '.log';
        parent::__construct($filesystem, $filePath);
    }
}
