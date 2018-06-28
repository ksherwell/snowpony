<?php
/**
 * Blackbird EstimateTimeShipping Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_EstimateTimeShipping
 * @copyright       Copyright (c) 2018 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://store.bird.eu/license/
 * @support         help@bird.eu
 */

namespace Blackbird\EstimateTimeShipping\Model;

use Magento\SalesRule\Model\Coupon\CodegeneratorFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;

/**
 * Class CartRule
 * @package Blackbird\EstimateTimeShipping\Model
 */
class CartRule extends Rule
{
    /**
     * @var \Magento\Rule\Model\Condition\CombineFactory
     */
    protected $conditionFactory;

    /**
     * CartRule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param CouponFactory $couponFactory
     * @param CodegeneratorFactory $codegenFactory
     * @param Rule\Condition\CombineFactory $condCombineFactory
     * @param Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Rule\Condition\CombineFactory $conditionFactory
     * @param null $resource
     * @param null $resourceCollection
     * @param array $data
     * @param null $extensionFactory
     * @param null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        CouponFactory $couponFactory,
        CodegeneratorFactory $codegenFactory,
        Rule\Condition\CombineFactory $condCombineFactory,
        Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Rule\Condition\CombineFactory $conditionFactory,
        $resource = null,
        $resourceCollection = null,
        array $data = [],
        $extensionFactory = null,
        $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->conditionFactory = $conditionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->conditionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('cart_conditions_serialized');

        $this->setConditions($conditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);

        if (isset($arr['cart_conditions_serialized'])) {
            $this->getConditions()->setConditions([])->loadArray(
                $arr['cart_conditions_serialized'][1],
                'cart_conditions_serialized'
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'cart_conditions_serialized' || $key === 'actions') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = &$arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = &$node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date'], true) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }
}
