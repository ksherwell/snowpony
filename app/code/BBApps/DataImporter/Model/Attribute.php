<?php
/**
 * BBApps DataImporter
 *
 * @useProtected
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Psr\Log\LoggerInterface;

use BBApps\DataImporter\Helper\Data;
use BBApps\DataImporter\Helper\ProductOptionHelper;

class Attribute extends AbstractModel
{
    const DEFAULT_ATTRIBUTE_SET = 'Default';
    const ALL_ATTRIBUTE_SETS_VALUE = 'all';
    const DEFAULT_ATTRIBUTE_GROUP = 'General';

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ProductOptionHelper
     */
    private $productOptionHelper;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        ProductAttributeRepositoryInterface $attributeRepository,
        Config $eavConfig,
        EavSetup $eavSetup,
        Data $helper,
        ProductOptionHelper $productOptionHelper,
        AttributeFactory $attributeFactory,
        array $data = []
    ) {
        parent::__construct($logger, $filesystem, $data);

        $this->attributeRepository = $attributeRepository;
        $this->eavConfig = $eavConfig;
        $this->eavSetup = $eavSetup;
        $this->helper = $helper;
        $this->productOptionHelper = $productOptionHelper;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * import attribute
     *
     * @param $data
     * @return bool
     */
    public function import($data)
    {
        $data = $this->_prepareData($data);
        $attributeOptions = $data['options'];
        unset($data['options']);
        if ($attribute = $this->saveAttribute($data)) {
            if (!empty($attributeOptions)) {
                $this->addAttributeOptions($attribute, $attributeOptions);
            }
            $this->assignAttributeSet($data, $attribute->getAttributeCode());

            return true;
        }

        return false;
    }

    /**
     * prepare data for importing
     *
     * @param $data
     * @return mixed
     */
    private function _prepareData($data)
    {
        $frontendLabels = [];
        if (isset($data['frontend_labels'])) {
            $frontendLabels = $this->convertStringToArray($data['frontend_labels']);
            unset($data['frontend_labels']);
        }
        if (!empty($data['default_label'])) {
            $frontendLabels[0] = $data['default_label'];
            unset($data['default_label']);
        }
        $data['default_frontend_label'] = $frontendLabels;

        $attributeOptions = [];
        if (isset($data['attribute_options'])) {
            $attributeOptions = $this->convertStringToArray($data['attribute_options']);
            unset($data['attribute_options']);
        }
        if (isset($data['options'])) {
            $attributeOptions = $this->convertStringToArray($data['options']);
        }
        $data['options'] = $attributeOptions;

        return $data;
    }

    /**
     * add attribute options
     *
     * @param ProductAttributeInterface $attribute
     * @param [] $attributeOptions
     */
    private function addAttributeOptions($attribute, $attributeOptions)
    {
        $oldOptions = [];
        foreach ($attribute->getOptions() as $option) {
            $_optionValue = $option->getLabel();
            if (!in_array($_optionValue, $oldOptions)) {
                $oldOptions[] = $_optionValue;
            }
        }
        $attributeCode = $attribute->getAttributeCode();
        $count = 0;
        foreach ($attributeOptions as $attributeOption) {
            if (!isset($attributeOption[0])) { // required for Admin value
                continue;
            }
            $isDefault = ($count == 0);
            if (!in_array($attributeOption[0], $oldOptions) &&
                $_optionObj = $this->productOptionHelper->createAttributeOptionObject($attributeOption, $isDefault)) {
                $this->productOptionHelper->addOption($attributeCode, $_optionObj);
                $count++;
            }
        }
    }

    /**
     * assign attribute set to attribute
     *
     * @param $data
     * @param $attributeCode
     */
    private function assignAttributeSet($data, $attributeCode)
    {
        $attributeSets = [self::DEFAULT_ATTRIBUTE_SET];
        if (!empty($data['attribute_set'])) {
            $attributeSets = explode('|', $data['attribute_set']);
        }
        if (in_array(self::ALL_ATTRIBUTE_SETS_VALUE, array_map('strtolower', $attributeSets))) {
            $attributeSets = $this->helper->getListAttributeSets();
        }

        $attributeGroup = self::DEFAULT_ATTRIBUTE_GROUP;
        if (!empty($data['attribute_group_name'])) {
            $attributeGroup = $data['attribute_group_name'];
        }

        // assign to attribute set
        foreach ($attributeSets as $attributeSet) {
            try {
                $groupValue = $this->helper->getGroupValue($attributeSet, $attributeGroup);

                $this->eavSetup->addAttributeToGroup(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attributeSet,
                    $groupValue,
                    $attributeCode
                );
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
    }

    /**
     * Small|Medium|Large
     * 0=Default:1=English:2=French:3=German
     * format: 0=Small:1=Small EN:2=Small EN|0=Medium:1=Medium EN:2=Medium EN
     * to [[0 => 'Small', 1 => 'Small EN', 2 => 'Small EN'], [0 => 'Medium', 1 => 'Medium EN', 2 => 'Medium EN']]
     *
     * @param $string
     * @return array
     */
    private function convertStringToArray($string)
    {
        $array = [];
        if ($string) {
            $values = explode('|', $string);
            foreach ($values as $value) {
                $tmpArr = [];
                if (strpos($value, ':') !== false) {
                    $storeValues = explode(':', $value);
                    foreach ($storeValues as $storeValue) {
                        if (strpos($storeValue, '=') !== false) {
                            list($k, $v) = explode('=', $storeValue, 2);
                        } else {
                            list($k, $v) = ['0', $storeValue];
                        }
                        $tmpArr[$k] = $v;
                    }
                } else {
                    if (strpos($value, '=') !== false) {
                        list($k, $v) = explode('=', $value, 2);
                    } else {
                        list($k, $v) = ['0', $value];
                    }
                    $tmpArr[$k] = $v;
                }

                $array[] = $tmpArr;
            }
        }

        return $array;
    }

    /**
     * save attribute
     *
     * @param $data
     * @return bool|ProductAttributeInterface
     */
    private function saveAttribute($data)
    {
        if (empty($data['attribute_code'])) {
            return false;
        }

        $data['attribute_code'] = $this->helper->formatAttributeCode($data['attribute_code']);

        $attribute = null;
        try {
            $attribute = $this->attributeRepository->get($data['attribute_code']);
        } catch (\Exception $e) {
            // do nothing
        }
        if (!$attribute) {
            $attribute = $this->attributeFactory->create();
        }

        $this->helper->setDataToObject($attribute, $data);
        try {
            $this->attributeRepository->save($attribute);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }

        return $attribute;
    }
}
