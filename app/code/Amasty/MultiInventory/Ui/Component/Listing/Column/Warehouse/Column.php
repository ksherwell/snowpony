<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Warehouse;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Column extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * Column constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        array $components = [],
        array $data = []
    ) {
        $this->whFactory = $whFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    public function prepare()
    {
        $config = $this->getData('config');
        $config['disabled'] = [$this->whFactory->create()->getDefaultId()];
        $this->setData('config', $config);

        parent::prepare();
    }
}
