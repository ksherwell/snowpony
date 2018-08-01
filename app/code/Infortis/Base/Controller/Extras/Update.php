<?php

namespace Infortis\Base\Controller\Extras;

use Magento\Framework\Controller\ResultFactory;


class Update extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productId = $this->getRequest()->getParam('itemid'); 
		$qty 	= $this->getRequest()->getParam('qty'); 
		$quote 	= $this->cart->getQuote();
		$_product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
		$quoteItem = $quote->getItemByProduct($_product);
		$response['status'] = true;
		try{
			
		if($quoteItem){
			if ($quoteItem->getId()) {
				$quoteItem->setQty((double) $qty);
				$quoteItem->getProduct()->setIsSuperMode(true);
				$quoteItem->save(); 
				$this->cart->getQuote()->collectTotals();
			}
		}else{
			$params = array(
				'form_key' => $objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
				'product' => $productId,
				'qty'   =>1               
			);              
			//Load the product based on productID   
			$_product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);       
			$additionalOptions['print_style'] = [
				'label' => __('Extras Item'),
				'value' => 1,
			];
			/* $_product->addCustomOption('additional_options', $objectManager->get('\Magento\Framework\Serialize\Serializer\Json')->serialize($additionalOptions)); */
			$this->cart->addProduct($_product, $params);
			$this->cart->save();
			$this->cart->getQuote()->collectTotals();
		}

		}catch(\Exception $e){
			$response['status'] = false;
			$response['message'] = $e->getMessage();
		}
		$imageQuoteData = $objectManager->get('Magento\Checkout\Model\Cart\ImageProvider')->getImages($quote->getId());
		$extrasProduct = $objectManager->get('Infortis\Base\Helper\Data')->getExtrasProduct();
		$response['extras'] = count($extrasProduct) > 0 ? $extrasProduct : false;
		$response['imageData'] = $imageQuoteData;
		/* {"226":{"src":"http:\/\/snowgoose.worlddigital.com.au\/media\/catalog\/product\/cache\/f485795eb4b45ff97c82d72651274f10\/s\/a\/savoury-indulgenceaerial.png","alt":"Savoury Indulgence","width":78,"height":78}, */
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);
        return $resultJson;
    }
}
