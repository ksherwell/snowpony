<?php
namespace Infortis\Base\Controller\Extras;
use Magento\Framework\Controller\ResultFactory;
class Delete extends \Magento\Checkout\Controller\Cart
{
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productId = $this->getRequest()->getParam('itemid'); 
		$_product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
		$quote 	= $this->cart->getQuote();
		$quoteItem = $quote->getItemByProduct($_product);
		if($quoteItem){
			$quoteItem->delete();//deletes
			$this->cart->getQuote()->collectTotals();
		}
		$imageQuoteData = $objectManager->get('Magento\Checkout\Model\Cart\ImageProvider')->getImages($quote->getId());
		$extrasProduct = $objectManager->get('Infortis\Base\Helper\Data')->getExtrasProduct();
		$response['extras'] = count($extrasProduct) > 0 ? $extrasProduct : false;
		$response['imageData'] = $imageQuoteData;
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);
        return $resultJson;
    }
}
