<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\ExportInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Export extends AbstractExtensibleModel implements ExportInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\MultiInventory\Model\ResourceModel\Export');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::EXPORT_ID);
    }

    /**
     * @return int
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_TIME);
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->setData(self::EXPORT_ID, $id);
    }

    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->setData(self::FILE, $file);
    }

    /**
     * @param $time
     */
    public function setCreateTime($time)
    {
        $this->setData(self::CREATE_TIME, $time);
    }

    public function getHeaders()
    {
        return ['sku', 'code', 'qty'];
    }
}
