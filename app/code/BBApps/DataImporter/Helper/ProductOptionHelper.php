<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Model\Entity\Attribute\OptionLabelFactory;

class ProductOptionHelper extends AbstractHelper
{
    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $optionManagement;

    /**
     * @var
     */
    private $attributeOptionFactory;

    /**
     * @var array
     */
    private $productOptions = [];

    /**
     * @var OptionLabelFactory
     */
    private $attributeOptionLabelFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private $attributeObj;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    private $storeOptionValues = [];

    public function __construct(
        Context $context,
        ProductAttributeOptionManagementInterface $optionManagement,
        OptionFactory $attributeOptionFactory,
        OptionLabelFactory $attributeOptionLabelFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);

        $this->optionManagement = $optionManagement;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attributeOptionLabelFactory = $attributeOptionLabelFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }

    /**
     * @return AttributeOptionInterface
     */
    public function createAttributeOption()
    {
        return $this->attributeOptionFactory->create();
    }

    /**
     * Check whether option label exists in option array
     *
     * @param $attributeCode
     * @param $optionLabel
     *
     * @return bool
     */
    public function isOptionLabelExist($attributeCode, $optionLabel)
    {
        return array_key_exists($optionLabel, $this->getOptionArrWithLabel($attributeCode));
    }

    /**
     * @param string $attributeCode
     * @param AttributeOptionInterface $option
     *
     * @throws \Magento\Framework\Exception\InputException
     *
     * @return bool
     */
    public function addOption($attributeCode, $option)
    {
        return $this->optionManagement->add($attributeCode, $option);
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     *
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     *
     * @return bool
     */
    public function deleteOption($attributeCode, $optionId)
    {
        return $this->optionManagement->delete($attributeCode, $optionId);
    }

    /**
     * Remove un-labeled options
     *
     * @param string $attributeCode
     *
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function removeEmptyOption($attributeCode)
    {
        $options = $this->optionManagement->getItems($attributeCode);

        if (!empty($options)) {
            foreach ($options as $option) {
                if (empty(trim($option->getLabel())) && !empty($option->getValue())) {
                    $this->deleteOption($attributeCode, $option->getValue());
                }
            }
        }
    }

    /**
     * @return AttributeOptionLabelInterface
     */
    public function createAttributeLabelOption()
    {
        return $this->attributeOptionLabelFactory->create();
    }

    /**
     * Get product attribute options as array with key = id
     *
     * @param string $attributeCode
     * @param bool $forceQuery : force calling from db
     *
     * @return AttributeOptionInterface[]: [optionKey => AttributeOptionInterface]
     */
    public function getOptionArrWithIds($attributeCode, $forceQuery = false)
    {
        $optionArr = [];

        // store options to protected field to prevent multiple queries when calling this function
        if ($forceQuery || count($this->productOptions) == 0) {
            try {
                $this->setAttribute($attributeCode);
                $this->productOptions = $this->getOptionCollection($this->getAttribute());
            } catch (\Exception $e) {
                return $optionArr;
            }
        }

        if (!empty($this->productOptions)) {
            /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $option */
            foreach ($this->productOptions as $option) {
                $storeLabels = $this->getOptionStoreLabels($option, $attributeCode);
                $option->setStoreLabels($storeLabels);
                $optionArr[$option->getId()] = $option;
            }
        }

        return $optionArr;
    }

    /**
     * Get product attribute options as array with key = label
     *
     * @param string $attributeCode
     * @param bool $forceQuery : force calling from db
     *
     * @return AttributeOptionInterface[]: [optionLabel => AttributeOptionInterface]
     */
    public function getOptionArrWithLabel($attributeCode, $forceQuery = false)
    {
        $optionArr = [];

        // store options to protected field to prevent multiple queries when calling this function
        if ($forceQuery || count($this->productOptions) == 0) {
            try {
                $this->setAttribute($attributeCode);
                $this->productOptions = $this->getOptionCollection($this->getAttribute());
            } catch (\Exception $e) {
                return $optionArr;
            }
        }

        if (!empty($this->productOptions)) {
            /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $option */
            foreach ($this->productOptions as $option) {
                $storeLabels = $this->getOptionStoreLabels($option, $attributeCode);
                $option->setStoreLabels($storeLabels);
                $optionArr[$option->getValue()] = $option;
            }
        }

        return $optionArr;
    }

    /**
     * @param $attributeCode
     * @return void
     */
    public function setAttribute($attributeCode)
    {
        if ($attributeCode) {
            $this->attributeObj = $this->productAttributeRepository->get($attributeCode);
        }
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute()
    {
        return $this->attributeObj;
    }

    /**
     * @param $attribute
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    public function getOptionCollection($attribute)
    {
        return $this->attrOptionCollectionFactory->create()
            ->setAttributeFilter($attribute->getId())
            ->setPositionOrder('asc', true)
            ->load();
    }

    /**
     * Use it to get store labels of attribute option
     *
     * @param $option
     * @param $attributeCode
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getOptionStoreLabels($option, $attributeCode)
    {
        $this->setAttribute($attributeCode);

        $values = [];
        foreach ($this->storeManager->getStores(true) as $store) {
            $values = array_merge(
                $values,
                $this->createStoreValues($store->getId(), $option->getId())
            );
        }

        return $values;
    }

    /**
     * Create store values
     *
     * @codeCoverageIgnore
     * @param integer $storeId
     * @param integer $optionId
     * @return array
     */
    private function createStoreValues($storeId, $optionId)
    {
        $value = [];
        $storeValues = $this->getStoreOptionValues($storeId);
        $swatchStoreValue = isset($storeValues['swatch']) ? $storeValues['swatch'] : null;
        $value['store' . $storeId] = isset($storeValues[$optionId]) ?
            $this->escaper->escapeHtml($storeValues[$optionId]) : '';
        $value['swatch' . $storeId] = isset($swatchStoreValue[$optionId]) ?
            $this->escaper->escapeHtml($swatchStoreValue[$optionId]) : '';

        return $value;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getStoreOptionValues($storeId)
    {
        $storeOptionValues = $this->storeOptionValues;
        if (!in_array($storeId, $storeOptionValues)) {
            $values = [];
            $valuesCollection = $this->attrOptionCollectionFactory->create();
            $valuesCollection->setAttributeFilter(
                $this->getAttribute()->getId()
            );
            $this->addCollectionStoreFilter($valuesCollection, $storeId);
            $valuesCollection->getSelect()->joinLeft( // @codingStandardsIgnoreLine: use sql in helper
                ['swatch_table' => $valuesCollection->getTable('eav_attribute_option_swatch')],
                'swatch_table.option_id = main_table.option_id AND swatch_table.store_id = '.$storeId,
                'swatch_table.value AS label'
            );
            $valuesCollection->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
                $values['swatch'][$item->getId()] = $item->getLabel();
            }

            $storeOptionValues[$storeId] = $values;
            $this->storeOptionValues = $storeOptionValues;
        }

        return $storeOptionValues[$storeId];
    }

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $valuesCollection
     * @param int $storeId
     * @return void
     */
    private function addCollectionStoreFilter($valuesCollection, $storeId)
    {
        $joinCondition = $valuesCollection->getConnection()->quoteInto(
            'tsv.option_id = main_table.option_id AND tsv.store_id = ?',
            $storeId
        );

        $select = $valuesCollection->getSelect();
        $select->joinLeft( // @codingStandardsIgnoreLine: use sql in helper
            ['tsv' => $valuesCollection->getTable('eav_attribute_option_value')],
            $joinCondition,
            'value'
        );
        if (\Magento\Store\Model\Store::DEFAULT_STORE_ID == $storeId) {
            $select->where( // @codingStandardsIgnoreLine: use sql in helper
                'tsv.store_id = ?',
                $storeId
            );
        }
        $valuesCollection->setOrder('value', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * create attribute option object
     *
     * @param [] $attributeOption
     * @param bool $isDefault
     * @return false|AttributeOptionInterface
     */
    public function createAttributeOptionObject($attributeOption, $isDefault)
    {
        if (isset($attributeOption[0])) { // required for Admin value
            $_optionLabels = [];
            foreach ($attributeOption as $_storeId => $_label) {
                /**
                 * AttributeOptionLabelInterface
                 */
                $_optionLabels[] = $this->createAttributeLabelOption()
                    ->setStoreId($_storeId)->setLabel($_label);
            }
            return $this->createAttributeOption()
                ->setIsDefault($isDefault)
                ->setStoreLabels($_optionLabels);
        }

        return false;
    }
}
