<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class CustomerGroup extends AbstractColumn
{

    private $collectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Amasty\MultiInventory\Model\WarehouseFactory $factory,
        \Amasty\MultiInventory\Api\WarehouseRepositoryInterface $repository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\MultiInventory\Helper\System $helper,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $warehouseStockResource,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $resource,
            $factory,
            $repository,
            $jsonEncoder,
            $helper,
            $warehouseStockResource,
            $components,
            $data
        );
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
        $origGroups = [];
        $collection = $this->repository->getById($item['warehouse_id'])->getCustomerGroups();
        if (count($collection)) {
            $origGroups = [];
            foreach ($collection as $group) {
                $origGroups[] = $group->getCode();
            }
        }
        if (!count($origGroups)) {
            return __('Not groups');
        }
        if (count($origGroups) == $this->collectionFactory->create()->getSize()) {
            return __('All Customer Groups');
        }
        foreach ($origGroups as $code) {
            $content .= $code . "<br/>";
        }

        return $content;
    }
}
