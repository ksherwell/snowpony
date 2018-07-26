<?php
namespace Infortis\Base\Controller\Giftmessage;
use Magento\Framework\Controller\ResultFactory;
class Update extends \Magento\Checkout\Controller\Cart
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
			/* 
				[gift_message_id] => 11 [customer_id] => 0 [sender] => ad11 [recipient] => dad [message] => testteka11
			*/
			if ($quoteItem->getGiftMessageId()) {
				$giftmessageModel->load($quoteItem->getGiftMessageId());
			}
			$giftmessageModel->setRecipient($params['gift-message-whole-to']);
			$giftmessageModel->setSender($params['gift-message-whole-from']);
			$giftmessageModel->setMessage($params['gift-message-whole-message']);
			$giftmessageModel->setCustomerId($customerId);
			
			$giftmessageModel->save();
			// save quote id gift message
			$quoteItem->setGiftMessageId($giftmessageModel->getId());
			$quoteItem->save();
			$response['status'] = true;
			$response['gift'] = $giftmessageModel->getData();
		}catch(\Exception $e){
			$response['status'] = false;
			$response['msg'] = $e->getMessage();
		}
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);
        return $resultJson;
    }
}
