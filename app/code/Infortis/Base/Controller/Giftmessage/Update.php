<?php
namespace Infortis\Base\Controller\Giftmessage;
use Magento\Framework\Controller\ResultFactory;
class Update extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$params = $this->getRequest()->getParams(); 
		$giftModel = $objectManager->get('Magento\GiftMessage\Model\Save');
		
		$giftmessage = $params[''];
		// $entityType = quote_item
		$giftModel->_saveOne($entityId, $giftmessage, 'quote_item');
		print_r($params); die();
		/* $quoteItem = $this->cart->getQuote()->getItemById($itemId);
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
        return $resultJson; */
    }
}
