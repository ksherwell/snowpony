<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */
namespace BBApps\DataImporter\Model\Import;

use Magento\CustomerImportExport\Model\Import;
use Magento\CustomerImportExport\Model\Import\AddressFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;

use BBApps\DataImporter\Model\Import\Customer;
use BBApps\DataImporter\Model\Import\CustomerFactory;
use BBApps\DataImporter\Model\ResourceModel\Import\CustomerComposite\DataFactory;

/**
 * Import entity customer combined model
 *
 */
class CustomerComposite extends Import\CustomerComposite
{
    const COLUMN_BILLING_ADDRESS_PREFIX = '_billing_';

    const COLUMN_SHIPPING_ADDRESS_PREFIX = '_shipping_';

    const COMPONENT_BILLING_ENTITY_ADDRESS = 'billing_address';

    const COMPONENT_SHIPPING_ENTITY_ADDRESS = 'shipping_address';

    private $_shippingAddressEntity;

    private $_billingAddressEntity;

    protected $_customerAttributes = [
        Customer::COLUMN_SUBSCRIBED
    ];

    /**
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param DataFactory $dataFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        DataFactory $dataFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        array $data = []
    ) {
        AbstractEntity::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );

        $this->addMessageTemplate(
            self::ERROR_ROW_IS_ORPHAN,
            __('Orphan rows that will be skipped due default row errors')
        );

        $this->_availableBehaviors = [
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        ];

        // customer entity stuff
        if (isset($data['customer_data_source_model'])) {
            $this->_dataSourceModels['customer'] = $data['customer_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
            ];
            $this->_dataSourceModels['customer'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['customer_entity'])) {
            $this->_customerEntity = $data['customer_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['customer'];
            $this->_customerEntity = $customerFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }
        $this->_initCustomerAttributes();

        // address entity stuff
        if (isset($data['address_data_source_model'])) {
            $this->_dataSourceModels['address'] = $data['address_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                'customer_attributes' => $this->_customerAttributes,
            ];
            $this->_dataSourceModels['address'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['address_entity'])) {
            $this->_addressEntity = $data['address_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['address'];
            $this->_addressEntity = $addressFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }

        // billing address
        if (isset($data['billing_address_data_source_model'])) {
            $this->_dataSourceModels['billing_address'] = $data['billing_address_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_BILLING_ENTITY_ADDRESS,
                'customer_attributes' => $this->_customerAttributes,
            ];
            $this->_dataSourceModels['billing_address'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['billing_address_entity'])) {
            $this->_billingAddressEntity = $data['billing_address_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['billing_address'];
            $this->_billingAddressEntity = $addressFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }

        // shipping address
        if (isset($data['shipping_address_data_source_model'])) {
            $this->_dataSourceModels['shipping_address'] = $data['shipping_address_data_source_model'];
        } else {
            $arguments = [
                'entity_type' => CustomerComposite::COMPONENT_SHIPPING_ENTITY_ADDRESS,
                'customer_attributes' => $this->_customerAttributes,
            ];
            $this->_dataSourceModels['shipping_address'] = $dataFactory->create(['arguments' => $arguments]);
        }
        if (isset($data['shipping_address_entity'])) {
            $this->_shippingAddressEntity = $data['shipping_address_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['shipping_address'];
            $this->_shippingAddressEntity = $addressFactory->create(['data' => $data]);
            unset($data['data_source_model']);
        }
        $this->_initAddressAttributes();

        // next customer id
        if (isset($data['next_customer_id'])) {
            $this->_nextCustomerId = $data['next_customer_id'];
        } else {
            $this->_nextCustomerId = $resourceHelper->getNextAutoincrement($this->_customerEntity->getEntityTable());
        }
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'custom_customer_composite';
    }

    /**
     * Is attribute contains particular data (not plain customer attribute)
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeParticular($attributeCode)
    {
        if (in_array(preg_replace(
            '/^(' . self::COLUMN_BILLING_ADDRESS_PREFIX . '|' . self::COLUMN_SHIPPING_ADDRESS_PREFIX . ')/',
            '',
            $attributeCode
        ), $this->_addressAttributes)) {
            return true;
        } else {
            return parent::isAttributeParticular($attributeCode);
        }
    }

    /**
     * Validate address row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateAddressRow(array $rowData, $rowNumber)
    {
        if ($this->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            return true;
        }

        $rowData = $this->_prepareAddressRowData($rowData);
        if (!empty($rowData)) {
            return $this->_validateAddressEntity($rowData, $rowNumber);
        }

        $billingRowData = $this->_prepareAddressRowData($rowData, self::COLUMN_BILLING_ADDRESS_PREFIX);
        if (!empty($billingRowData)) {
            return $this->_validateAddressEntity($rowData, $rowNumber);
        }

        $shippingRowData = $this->_prepareAddressRowData($rowData, self::COLUMN_SHIPPING_ADDRESS_PREFIX);
        if (!empty($shippingRowData)) {
            return $this->_validateAddressEntity($rowData, $rowNumber);
        }

        return true;
    }

    protected function _validateAddressEntity(array $rowData, $rowNumber)
    {
        $rowData[Import\Address::COLUMN_WEBSITE] = $this->_currentWebsiteCode;
        $rowData[Import\Address::COLUMN_EMAIL] = $this->_currentEmail;
        $rowData[Import\Address::COLUMN_ADDRESS_ID] = null;

        return $this->_addressEntity->validateRow($rowData, $rowNumber);
    }

    protected function _prepareAddressRowData(array $rowData, $prefix = null)
    {
        $excludedAttributes = [self::COLUMN_DEFAULT_BILLING, self::COLUMN_DEFAULT_SHIPPING];

        unset(
            $rowData[Import\Customer::COLUMN_WEBSITE],
            $rowData[Import\Customer::COLUMN_STORE]
        );

        $result = [];
        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes) && !empty($value)) {
                if (!in_array($key, $excludedAttributes)) {
                    if (strpos($key, $prefix) === 0) {
                        $key = str_replace($prefix, '', $key);
                    } else {
                        $key = str_replace(self::COLUMN_ADDRESS_PREFIX, '', $key);
                    }
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Import data rows
     *
     * @return bool
     */
    protected function _importData()
    {
        $result = $this->_customerEntity->importData();
        if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            return $result && $this->_addressEntity->setCustomerAttributes($this->_customerAttributes)->importData()
                && $this->_billingAddressEntity->setCustomerAttributes($this->_customerAttributes)->importData()
                && $this->_shippingAddressEntity->setCustomerAttributes($this->_customerAttributes)->importData();
        }

        return $result;
    }
}
