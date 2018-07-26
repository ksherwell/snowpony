<?php
namespace Infortis\Base\Controller\Giftmessage;
use Magento\Framework\Controller\ResultFactory;
class Delete extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$params = $this->getRequest()->getParams(); 
		
		$itemId = $params['itemId'];
		$quoteItem = $this->cart->getQuote()->getItemById($itemId);
		$customerId = $this->cart->getQuote()->getCustomer()->getId();
		$giftmessageModel = $objectManager->get('\Magento\GiftMessage\Model\Message');
		try{
			if ($quoteItem->getGiftMessageId()) {
				$giftmessageModel->load($quoteItem->getGiftMessageId());
			}
			$giftmessageModel->delete();
			$quoteItem->setGiftMessageId(null);
			$quoteItem->save();
			$response['status'] = true;
		}catch(\Exception $e){
			$response['status'] = false;
			$response['msg'] = $e->getMessage();
		}
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);
        return $resultJson;
    }
}
