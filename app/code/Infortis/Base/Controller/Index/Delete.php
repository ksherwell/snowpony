<?php
namespace Infortis\Base\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
class Delete extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$itemId = $this->getRequest()->getParam('itemid'); 
		$quoteItem = $this->cart->getQuote()->getItemById($itemId);
		$response = array();
		if($quoteItem){
			
			if($this->cart->getQuote()->getItemsCount() > 1){
				if ($quoteItem->getId()) {
					$quoteItem->delete();//deletes
					$this->cart->getQuote()->collectTotals();
				}
			}else{
				$this->cart->truncate();
				$this->cart->saveQuote();
				$response['redirectUrl'] = $this->_url->getUrl('checkout/cart');
			}
		}
		$extrasProduct = $objectManager->get('Infortis\Base\Helper\Data')->getExtrasProduct();
		$response['extras'] = count($extrasProduct) > 0 ? $extrasProduct : false;
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);
        return $resultJson;
    }
}
