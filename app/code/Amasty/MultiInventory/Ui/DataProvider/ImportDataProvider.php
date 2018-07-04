<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\DataProvider;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import\CollectionFactory;

class ImportDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * ImportDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $number = $this->request->getParam('import_number');

        $config = $this->getConfigData();
        if (!$this->getCollection()->isLoaded()) {
            if ($number) {
                $config['params']['import_number'] = $number;
                $this->setConfigData($config);
                $this->getCollection()->addFieldToFilter('import_number', $number);
            }
            $this->getCollection()->joinProducts();
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return $items;
    }
}
