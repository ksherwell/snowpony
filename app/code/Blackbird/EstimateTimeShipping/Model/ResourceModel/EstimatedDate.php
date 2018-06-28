<?php
/**
 * Blackbird EstimateTimeShipping Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_EstimateTimeShipping
 * @copyright       Copyright (c) 2018 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://store.bird.eu/license/
 * @support         help@bird.eu
 */

namespace Blackbird\EstimateTimeShipping\Model\ResourceModel;

use Blackbird\EstimateTimeShipping\Api\Data\EstimatedDateInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class EstimatedDate
 * @package Blackbird\EstimateTimeShipping\Model\ResourceModel
 */
class EstimatedDate extends AbstractDb
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init('blackbird_ets_estimated_date', EstimatedDateInterface::ID);
    }

    /**
     * Get saved order estimated date with the max estimated item delivery date
     *
     * @param $orderId
     * @return array
     */
    public function getOrderMaxEstimatedDate($orderId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            $this->getTable('blackbird_ets_estimated_date'),
            ['MAX(date)', 'is_delivery']
        )->where('order_id = ' . $orderId);

        $dateInformation = $connection->fetchAll($select);

        return [
            'date'        => $dateInformation[0]['MAX(date)'],
            'is_delivery' => $dateInformation[0]['is_delivery']
        ];
    }
}
