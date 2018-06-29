<?php
/**
 * BBApps DataImporter
 *
 * @copyright  Copyright (c) 2017 BBApps (https://doublebapps.com/)
 */

namespace BBApps\DataImporter\Helper;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(
        Context $context,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EavSetup $eavSetup
    ) {
        parent::__construct($context);
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eavSetup = $eavSetup;
    }

    /**
     * set data to object
     * from format value_id to setValueId()
     *
     * @param $object
     * @param $data
     */
    public function setDataToObject($object, $data)
    {
        foreach ($data as $key => $value) {
            $key = implode('', array_map('ucfirst', explode('_', $key)));
            $function = 'set'. $key;
            if (method_exists($object, $function)) {
                $object->$function($value);
            }
        }
    }

    /**
     * format attribute code
     *
     * @param $string
     * @return mixed
     */
    public function formatAttributeCode($string)
    {
        $string = CharacterHelper::removeUnicodeWhitespace($string);
        $string = CharacterHelper::utf8ToSimilarAscii($string);
        $string = CharacterHelper::removeSpecialCharacter($string, '_');
        $string = preg_replace('/\_+/', '_', $string);
        $string = strtolower($string);

        return $string;
    }

    /**
     * get list of attribute sets
     *
     * @return array
     */
    public function getListAttributeSets()
    {
        $list = [];
        $attributeSets = $this->attributeSetRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($attributeSets as $attributeSet) {
            $list[] = $attributeSet->getAttributeSetName();
        }

        return $list;
    }

    /**
     * get group value
     *
     * @param $attributeSet
     * @param $attributeGroup
     * @return mixed
     */
    public function getGroupValue($attributeSet, $attributeGroup)
    {
        $groupId = $this->eavSetup->getAttributeGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSet,
            $attributeGroup,
            'attribute_group_id'
        );
        if (!is_numeric($groupId)) { // create a new group if it isn't exist
            $this->eavSetup->addAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSet,
                $attributeGroup
            );

            return $attributeGroup;
        }

        return $groupId;
    }
}
