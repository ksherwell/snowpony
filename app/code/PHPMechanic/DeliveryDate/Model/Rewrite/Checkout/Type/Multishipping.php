<?php

    namespace PHPMechanic\DeliveryDate\Model\Rewrite\Checkout\Type;
 
    class Multishipping extends \Magento\Multishipping\Model\Checkout\Type\Multishipping
    {


	    protected function _validate()
	    {

	        $quote = $this->getQuote();

	        /** @var $paymentMethod \Magento\Payment\Model\Method\AbstractMethod */
	        $paymentMethod = $quote->getPayment()->getMethodInstance();
	        if (!$paymentMethod->isAvailable($quote)) {
	            throw new \Magento\Framework\Exception\LocalizedException(
	                __('Please specify a payment method.')
	            );
	        }
/*
	        $addresses = $quote->getAllShippingAddresses();
	        foreach ($addresses as $address) {
//				$address->setShouldIgnoreValidation(true);
	            $addressValidation = $address->validate();
	            if ($addressValidation !== true) {
	                throw new \Magento\Framework\Exception\LocalizedException(
	                    __('Please check shipping addresses information.')
	                );
	            }
	            $method = $address->getShippingMethod();
	            $rate = $address->getShippingRateByCode($method);
	            if (!$method || !$rate) {
	                throw new \Magento\Framework\Exception\LocalizedException(
	                    __('Please specify shipping methods for all addresses.')
	                );
	            }
	        }

//			$quote->getBillingAddress()->setShouldIgnoreValidation(true);
	        $addressValidation = $quote->getBillingAddress()->validate();
	        if ($addressValidation !== true) {
	            throw new \Magento\Framework\Exception\LocalizedException(__('Please check billing address information.'));
	        }
*/
	        return $this;
	    }


	}