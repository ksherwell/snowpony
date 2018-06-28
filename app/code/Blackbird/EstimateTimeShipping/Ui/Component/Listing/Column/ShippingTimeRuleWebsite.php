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

namespace Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column;

use Blackbird\EstimateTimeShipping\Model\ResourceModel\ShippingTimeRule;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\WebsiteRepository;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ShippingTimeRuleWebsite
 * @package Blackbird\EstimateTimeShipping\Ui\Component\Listing\Column
 */
class ShippingTimeRuleWebsite extends Column
{
    /**
     * @var ShippingTimeRule
     */
    protected $shippingTimeRuleResource;

    /**
     * @var WebsiteRepository
     */
    protected $websiteRepo;

    /**
     * ShippingTimeRuleWebsite constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ShippingTimeRule $shippingTimeRuleResource
     * @param WebsiteRepository $websiteRepo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ShippingTimeRule $shippingTimeRuleResource,
        WebsiteRepository $websiteRepo,
        array $components = [],
        array $data = []
    ) {
        $this->websiteRepo              = $websiteRepo;
        $this->shippingTimeRuleResource = $shippingTimeRuleResource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {

        $content = '';

        $origRules = $item['shipping_time_rule_id'];

        $websiteIds = $this->shippingTimeRuleResource->lookupWebsiteIds($origRules);

        foreach ($websiteIds as $websiteId) {
            $content .= $this->websiteRepo->getById($websiteId)->getName() . "<br/>";
        }

        return $content;
    }
}
