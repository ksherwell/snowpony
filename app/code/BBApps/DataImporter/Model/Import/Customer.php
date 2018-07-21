<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2018 BBApps (https://doublebapps.com/)
 */
namespace BBApps\DataImporter\Model\Import;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\CustomerImportExport\Model\Import\Customer as ImportCustomer;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class Customer extends ImportCustomer
{
    const COLUMN_SUBSCRIBED = 'subscribed';

    const SUBSCRIBED_KEY = 'subscribed_key';

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        Config $eavConfig,
        StorageFactory $storageFactory,
        CollectionFactory $attrCollectionFactory,
        CustomerFactory $customerFactory,
        SubscriberFactory $subscriberFactory,
        array $data = []
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $storageFactory,
            $attrCollectionFactory,
            $customerFactory,
            $data
        );
        $this->subscriberFactory = $subscriberFactory;
    }

    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];
            $subscriberSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == Import::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->_getCustomerId(
                        $rowData[self::COLUMN_EMAIL],
                        $rowData[self::COLUMN_WEBSITE]
                    );
                } elseif ($this->getBehavior($rowData) == Import::BEHAVIOR_ADD_UPDATE) {
                    $processedData = $this->_prepareDataForUpdate($rowData);
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                    $subscriberSave = $subscriberSave + $processedData[self::SUBSCRIBED_KEY];
                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        if (!isset($attributesToSave[$tableName])) {
                            $attributesToSave[$tableName] = [];
                        }
                        $attributesToSave[$tableName] = array_diff_key(
                                $attributesToSave[$tableName],
                                $customerAttributes
                            ) + $customerAttributes;
                    }
                }
            }
            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
            /**
             * Save prepared data
             */
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->saveCustomerSubscribers($subscriberSave);
                $this->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
            }
            if ($attributesToSave) {
                $this->_saveCustomerAttributes($attributesToSave);
            }
            if ($entitiesToDelete) {
                $this->_deleteCustomerEntities($entitiesToDelete);
            }
        }

        return true;
    }

    /**
     * Prepare customer data for update
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $attributesToSave = [];
        $subscriberSave = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $emailInLowercase = strtolower($rowData[self::COLUMN_EMAIL]);
        $newCustomer = false;
        $entityId = $this->_getCustomerId($emailInLowercase, $rowData[self::COLUMN_WEBSITE]);
        if (!$entityId) {
            // create
            $newCustomer = true;
            $entityId = $this->_getNextEntityId();
            $this->_newCustomers[$emailInLowercase][$rowData[self::COLUMN_WEBSITE]] = $entityId;
        }

        $entityRow = [
            'group_id' => empty($rowData['group_id']) ? self::DEFAULT_GROUP_ID : $rowData['group_id'],
            'store_id' => empty($rowData[self::COLUMN_STORE]) ? 0 : $this->_storeCodeToId[$rowData[self::COLUMN_STORE]],
            'created_at' => $createdAt->format(DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(DateTime::DATETIME_PHP_FORMAT),
            'entity_id' => $entityId,
        ];

        // add subscriber
        if (isset($rowData[self::COLUMN_SUBSCRIBED])) {
            $subscriberSave[$entityId] = $rowData[self::COLUMN_SUBSCRIBED];
        }

        // password change/set
        if (isset($rowData['password']) && strlen($rowData['password'])) {
            $rowData['password_hash'] = $this->_customerModel->hashPassword($rowData['password']);
        }

        // attribute values
        foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $value) {
            if ($newCustomer && !strlen($value)) {
                continue;
            }

            $attributeParameters = $this->_attributes[$attributeCode];
            if ('select' == $attributeParameters['type']) {
                $value = isset($attributeParameters['options'][strtolower($value)])
                    ? $attributeParameters['options'][strtolower($value)]
                    : 0;
            } elseif ('datetime' == $attributeParameters['type'] && !empty($value)) {
                $value = (new \DateTime())->setTimestamp(strtotime($value));
                $value = $value->format(DateTime::DATETIME_PHP_FORMAT);
            }

            if (!$this->_attributes[$attributeCode]['is_static']) {
                /** @var $attribute \Magento\Customer\Model\Attribute */
                $attribute = $this->_customerModel->getAttribute($attributeCode);
                $backendModel = $attribute->getBackendModel();
                if ($backendModel
                    && $attribute->getFrontendInput() != 'select'
                    && $attribute->getFrontendInput() != 'datetime') {
                    $attribute->getBackend()->beforeSave($this->_customerModel->setData($attributeCode, $value));
                    $value = $this->_customerModel->getData($attributeCode);
                }
                $attributesToSave[$attribute->getBackend()
                    ->getTable()][$entityId][$attributeParameters['id']] = $value;

                // restore 'backend_model' to avoid default setting
                $attribute->setBackendModel($backendModel);
            } else {
                $entityRow[$attributeCode] = $value;
            }
        }

        if ($newCustomer) {
            // create
            $entityRow['website_id'] = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];
            $entityRow['email'] = $emailInLowercase;
            $entityRow['is_active'] = 1;
            $entitiesToCreate[] = $entityRow;
        } else {
            // edit
            $entitiesToUpdate[] = $entityRow;
        }

        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
            self::ATTRIBUTES_TO_SAVE_KEY => $attributesToSave,
            self::SUBSCRIBED_KEY => $subscriberSave
        ];
    }

    /**
     * save customer subscriber
     *
     * @param $subscriberSave
     */
    private function saveCustomerSubscribers($subscriberSave)
    {
        if (!empty($subscriberSave)) {
            foreach ($subscriberSave as $customerId => $isSubscribed) {
                $subscriber = $this->subscriberFactory->create();
                if ($isSubscribed) {
                    $subscriber->subscribeCustomerById($customerId);
                } else {
                    $subscriber->unsubscribeCustomerById($customerId);
                }
            }
        }
    }
}
