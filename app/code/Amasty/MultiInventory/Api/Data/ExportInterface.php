<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface ExportInterface
{
    const EXPORT_ID = 'export_id';

    const FILE = 'file';

    const CREATE_TIME = 'create_time';

    const PATH_EXPORT = 'amasty_export_stock/';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return string
     */
    public function getCreateTime();

    /**
     * @param $id
     */
    public function setId($id);

    /**
     * @param $file
     */
    public function setFile($file);

    /**
     * @param $time
     */
    public function setCreateTime($time);
}
