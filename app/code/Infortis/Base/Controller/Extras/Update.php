<?php

namespace Infortis\Base\Controller\Extras;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Cart\ImageProvider;
use Magento\Framework\Controller\ResultFactory;


class Update extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;
    /**
     * @var ImageProvider
     */
    private $imageProvider;
    /**
     * @var \Infortis\Base\Helper\Data
     */
    private $infortisHelperData;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializerJson;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formKey,
        ImageProvider $imageProvider,
        \Infortis\Base\Helper\Data $infortisHelperData,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);

        $this->product = $product;
        $this->formKey = $formKey;
        $this->imageProvider = $imageProvider;
        $this->infortisHelperData = $infortisHelperData;
        $this->serializerJson = $serializerJson;
    }

    public function execute()
    {
		$productId = $this->getRequest()->getParam('itemid');
		$qty 	= $this->getRequest()->getParam('qty');
		$quote 	= $this->cart->getQuote();
		$_product = $this->product->load($productId);
		$quoteItem = $quote->getItemByProduct($_product);
		$response['status'] = true;

        try {
            if ($quoteItem) {
                if ($quoteItem->getId()) {
                    $quoteItem->delete();
                }
            }

            $params = array(
                'form_key' => $this->formKey->getFormKey(),
                'product' => $productId,
                'qty' => isset($qty) ? $qty : 1,
            );

            $this->cart->addProduct($_product, $params);
            $this->cart->save();

        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }

		$imageQuoteData = $this->imageProvider->getImages($quote->getId());
		$extrasProduct = $this->infortisHelperData->getExtrasProduct();
		$response['extras'] = count($extrasProduct) > 0 ? $extrasProduct : false;
		$response['imageData'] = $imageQuoteData;
		/* {"226":{"src":"http:\/\/snowgoose.worlddigital.com.au\/media\/catalog\/product\/cache\/f485795eb4b45ff97c82d72651274f10\/s\/a\/savoury-indulgenceaerial.png","alt":"Savoury Indulgence","width":78,"height":78}, */
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($response);

        return $resultJson;
    }
}

