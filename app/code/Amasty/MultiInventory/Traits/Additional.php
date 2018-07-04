<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Traits;

trait Additional
{

    /**
     * @param $collection
     * @return \Generator
     */
    public function partCollection($collection)
    {
        foreach ($collection as $element) {
            yield $element;
        }
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    static function sortPriority($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }
}
