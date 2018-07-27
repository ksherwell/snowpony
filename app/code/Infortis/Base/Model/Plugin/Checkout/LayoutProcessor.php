<?php
namespace Infortis\Base\Model\Plugin\Checkout;
class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
 
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['package_address_type'] = [
            'component' => 'Magento_Checkout/js/view/package-address-type',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'Magento_Checkout/custom/form/package_address_type',
                'options' => [],
                'id' => 'package_address_type'
            ],
            'dataScope' => 'shippingAddress.package_address_type',
            'label' => __('Package Address Type'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 55,
            'id' => 'package_address_type'
        ];
 
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['package_address'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
				'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'package_address'
            ],
            'dataScope' => 'shippingAddress.package_address',
            'label' => __('Address'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 65,
            'id' => 'package_address'
        ];
 
 
        return $jsLayout;
    }
}