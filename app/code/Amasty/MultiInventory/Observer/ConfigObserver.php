<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Model\Export\ConvertToCsv;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;

class ConfigObserver implements ObserverInterface
{

    /**
     * @var \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor
     */
    private $stockProcessor;

    /**
     * @var ConvertToCsv
     */
    private $converter;

    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $factory;

    /**
     * @var \Amasty\MultiInventory\Model\WarehouseFactory
     */
    private $whFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Config\Model\Config\Factory
     */
    private $configFactory;

    private $cacheField;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;

    /**
     * ConfigObserver constructor.
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $stockProcessor
     * @param ConvertToCsv $converter
     * @param \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory
     * @param \Amasty\MultiInventory\Model\WarehouseFactory $whFactory
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $stockProcessor,
        ConvertToCsv $converter,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory,
        \Amasty\MultiInventory\Model\WarehouseFactory $whFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone
    ) {
        $this->stockProcessor = $stockProcessor;
        $this->converter = $converter;
        $this->factory = $factory;
        $this->whFactory = $whFactory;
        $this->messageManager = $messageManager;
        $this->configFactory = $configFactory;
        $this->timezone = $timezone;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getDataObject();

        $this->changeEnableMulti($object);
        $this->changeDecreaseStock($object);

        return $this;
    }

    /**
     * @param $object
     */
    private function changeEnableMulti($object)
    {
        if ($object->getPath() == \Amasty\MultiInventory\Helper\System::CONFIG_ENABLE_MULTI
            && $object->isValueChanged()
        ) {
            if ($object->getValue()) {
                $this->whFactory->create()->getResource()->setDatafromInventory($this->whFactory->create()->getDefaultId());
            } else {
                try {
                    $name = $this->timezone->date()->format('Y_m_d_H_i_s');
                    $filename = 'export_' . $name . '.csv';
                    $collection = $this->factory->create()->getResource()->getItemsExport();
                    $file = $this->converter->getFileFromCollection($filename, $collection);
                    $this->messageManager->addSuccessMessage(__('Export Inventory to ') . $file['value']);
                    $this->factory->create()->getResource()->deleteItems();
                    $this->whFactory->create()->getResource()->updateManages();
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Export not work.'));
                }
            }
        }
    }

    /**
     * @param $object
     */
    private function changeDecreaseStock($object)
    {
        $pathes = [
            \Amasty\MultiInventory\Helper\System::CONFIG_DECREASE_STOCK,
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_CAN_SUBTRACT
        ];

        if (in_array($object->getPath(), $pathes) && !$this->getCacheField()) {
            $pathDiff = array_diff($pathes, [$object->getPath()]);
            $path = null;
            foreach ($pathDiff as $value) {
                $path = $value;
            }
            $this->setCacheField($path);
            $options = explode("/", $path);

            $section = $options[0];
            $website = null;
            $store = null;
            $groups = [
                $options[1] =>
                    [
                        'fields' => [
                            $options[2] => [
                                'value' => $object->getValue()
                            ]
                        ]
                    ]
            ];
            $configData = [
                'section' => $section,
                'website' => $website,
                'store' => $store,
                'groups' => $groups,
            ];
            $configModel = $this->configFactory->create(['data' => $configData]);
            $configModel->save();
        }
    }

    /**
     * @return mixed
     */
    public function getCacheField()
    {
        return $this->cacheField;
    }

    /**
     * @param $path
     */
    public function setCacheField($path)
    {
        $this->cacheField = $path;
    }
}
