<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model\Import;

class AttributeOption extends ExtendedAbstractEntity
{
    /**
     * Import attribute code
     */
    const FIELD_NAME_ATTRIBUTE_CODE = '_attribute_code';

    private $storeIds;

    /**
     * @var \BBApps\DataImporter\Model\AttributeOption
     */
    private $attributeOption;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \BBApps\DataImporter\Model\AttributeOption $attributeOption,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator
        );

        $this->attributeOption = $attributeOption;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'attribute_option';
    }

    /**
     *
     * Multiple value separator getter.
     * @return string
     */
    public function getAttributeCode()
    {
        if (!empty($this->_parameters[self::FIELD_NAME_ATTRIBUTE_CODE])) {
            return $this->_parameters[self::FIELD_NAME_ATTRIBUTE_CODE];
        }

        return false;
    }

    protected function _importData()
    {
        parent::_importData();

        if (!$this->getAttributeCode()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Attribute Code Not Found'));
        }

        $attribute = null;
        try {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->productAttributeRepository->get($this->getAttributeCode());
        } catch (\Exception $e) {
            // do nothing
        }
        if (!$attribute) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Attribute Code Invalid'));
        }

        return $this->saveData();
    }

    private function saveData()
    {
        $data = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $data[] = $this->formatRowData($rowData);
            }
        }

        return $this->attributeOption->bulkImportAttributeOption($data, $this->getAttributeCode());
    }

    protected function formatRowData($rowData)
    {
        $rowDataFormat = [];
        foreach ($rowData as $key => $value) {
            if (is_numeric($key)) {
                $rowDataFormat[$key] = $value;
            } elseif ($this->getStoreIdByName($key) !== null) {
                $rowDataFormat[$this->getStoreIdByName($key)] = $value;
            } elseif ($key == 'swatch') {
                $rowDataFormat['swatch'] = $value;
            }
        }

        return $rowDataFormat;
    }

    protected function getStoreIdByName($name)
    {
        if (in_array($name, $this->getStoreIds())) {
            return array_search($name, $this->getStoreIds());
        }

        return null;
    }

    protected function getStoreIds()
    {
        if (!$this->storeIds) {
            $storeIds = [];
            $stores = $this->storeManager->getStores(true);
            foreach ($stores as $store) {
                $storeIds[$store->getId()] = strtolower($store->getName());
            }
            $this->storeIds = $storeIds;
        }

        return $this->storeIds;
    }
}
