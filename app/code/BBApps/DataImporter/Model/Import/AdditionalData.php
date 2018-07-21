<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model\Import;

use Magento\ImportExport\Model\Import\Entity\AbstractEntity;

class AdditionalData extends ExtendedAbstractEntity
{
    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        'sku' => 'sku'
    ];

    /**
     * @var \BBApps\DataImporter\Model\ProductImport
     */
    private $productImport;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * AdditionalData constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @param \BBApps\DataImporter\Model\AdditionalData $productImport
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \BBApps\DataImporter\Model\AdditionalData $productImport,
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

        $this->storeManager  = $storeManager;
        $this->productImport = $productImport;
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'additional_data';
    }

    /**
     * Create Product entity from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        $this->_validatedRows = null;

        return $this->saveProductData();
    }

    private function saveProductData()
    {
        $storeId = $this->storeManager->getStore()->getId();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                if (! $this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $this->productImport->import($rowData, $storeId);
            }
        }

        return true;
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return ! $this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        return ! $this->getErrorAggregator()->isRowInvalid($rowNum);
    }
}
