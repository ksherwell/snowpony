<?php

namespace Infortis\Base\Controller\Index;

use Magento\Framework\Controller\ResultFactory;


class Update extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$itemId = $this->getRequest()->getParam('itemid'); 
		$qty 	= $this->getRequest()->getParam('qty'); 
		$quoteItem = $this->cart->getQuote()->getItemById($itemId);

		if ($quoteItem->getId()) {
			$quoteItem->setQty((double) $qty);
			$quoteItem->getProduct()->setIsSuperMode(true);
			$quoteItem->save(); 
			$this->cart->getQuote()->collectTotals();
		}

		$extrasProduct = $objectManager->get('Infortis\Base\Helper\Data')->getExtrasProduct();
		$response['status'] = true;
		$response['extras'] = count($extrasProduct) > 0 ? $extrasProduct : false;
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);

        return $resultJson;
    }
}
