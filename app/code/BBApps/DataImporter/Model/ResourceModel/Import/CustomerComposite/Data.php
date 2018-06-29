<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */
namespace BBApps\DataImporter\Model\ResourceModel\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\Data as BaseData;
use BBApps\DataImporter\Model\Import\CustomerComposite;

class Data extends BaseData
{
    /**
     * Prepare row
     *
     * @param array $rowData
     * @return array|null
     */
    protected function _prepareRow(array $rowData)
    {
        switch ($this->_entityType) {
            case CustomerComposite::COMPONENT_ENTITY_CUSTOMER:
                if ($rowData['_scope'] == CustomerComposite::SCOPE_DEFAULT) {
                    return $rowData;
                } else {
                    return null;
                }
                break;
            case CustomerComposite::COMPONENT_BILLING_ENTITY_ADDRESS:
                return $this->_prepareAddressRowData($rowData, CustomerComposite::COLUMN_BILLING_ADDRESS_PREFIX);
            case CustomerComposite::COMPONENT_SHIPPING_ENTITY_ADDRESS:
                return $this->_prepareAddressRowData($rowData, CustomerComposite::COLUMN_SHIPPING_ADDRESS_PREFIX);
            default:
                return $this->_prepareAddressRowData($rowData, CustomerComposite::COLUMN_ADDRESS_PREFIX);
        }
    }

    /**
     * Prepare data row for address entity validation or import
     *
     * @param array $rowData
     * @param null $prefix
     * @return array
     */
    protected function _prepareAddressRowData(array $rowData, $prefix = null)
    {
        $excludedAttributes = [
            CustomerComposite::COLUMN_DEFAULT_BILLING,
            CustomerComposite::COLUMN_DEFAULT_SHIPPING,
        ];

        $excludeAddressPrefixes = [];
        if ($prefix != CustomerComposite::COLUMN_ADDRESS_PREFIX) {
            $excludeAddressPrefixes[] = CustomerComposite::COLUMN_ADDRESS_PREFIX;
        }
        if ($prefix != CustomerComposite::COLUMN_BILLING_ADDRESS_PREFIX) {
            $excludeAddressPrefixes[] = CustomerComposite::COLUMN_BILLING_ADDRESS_PREFIX;
        }
        if ($prefix != CustomerComposite::COLUMN_SHIPPING_ADDRESS_PREFIX) {
            $excludeAddressPrefixes[] = CustomerComposite::COLUMN_SHIPPING_ADDRESS_PREFIX;
        }

        $result = [];
        $hasAddressValue = false;
        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes) && !in_array($key, $excludedAttributes)) {
                if ($prefix) {
                    // skip the value of other types of address
                    if (preg_match('/^(' . implode('|', $excludeAddressPrefixes) . ')/', $key)) {
                        continue;
                    }

                    if (!$hasAddressValue && strpos($key, $prefix) === 0) {
                        $hasAddressValue = true;
                    }

                    $key = str_replace($prefix, '', $key);
                }
                $result[$key] = $value;
            }
        }

        if ($hasAddressValue && $prefix == CustomerComposite::COLUMN_BILLING_ADDRESS_PREFIX) {
            $result[CustomerComposite::COLUMN_DEFAULT_BILLING] = 1;
        }
        if ($hasAddressValue && $prefix == CustomerComposite::COLUMN_SHIPPING_ADDRESS_PREFIX) {
            $result[CustomerComposite::COLUMN_DEFAULT_SHIPPING] = 1;
        }

        return $result;
    }
}
