<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Warehouse;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;

class GroupOptions implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $currentOptions = [];

    /**
     * GroupOptions constructor.
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory
     * @param Escaper $escaper
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory,
        Escaper $escaper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $groups = $this->collectionFactory->create();
        foreach ($groups as $group) {
            $name = $this->escaper->escapeHtml($group->getCode());
            $this->currentOptions[$name] = ['label' => __($name), 'value' => $group->getId()];
        }

        $this->options = array_values($this->currentOptions);
        return $this->options;
    }
}
