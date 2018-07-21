<?php
namespace PHPMechanic\CheckPostcode\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{	
		$request = $this->getRequest()->getParams();
		$postcode = $request['postcode'];
		$dairy = $request['dairy'];

		if (isset($postcode) && isset($dairy)) {
			// https://postcode.auspost.com.au/free_display.html?id=1
			$australia['New South Wales'] = array_merge(range(1000,2599),range(2620,2899),range(2921,2999));
			$australia['Victoria'] = array_merge(range(3000,3999),range(8000,8999));
			$australia['Queensland'] = array_merge(range(4000,4999),range(9000,9999));
			$australia['South Australia'] = range(5000,5999);
			$australia['Western Australia'] = range(6000,6999);
			$australia['Tasmania']  = range(7000,7999);
			$australia['Australian Capital Territory']  = array_merge(range(200,299),range(2600,2619),range(2900,2920));
			$australia['Northern Territory']  = range(800,999);

			if ($dairy) $deliveryStates = array('New South Wales', 'Victoria', 'Queensland', 'South Australia', 'Western Australia');
			else $deliveryStates = array('New South Wales', 'Victoria', 'Queensland', 'Western Australia');

			$error = false;
			if (strlen($postcode) !== 4) $error = true;
			if ($error) die('-1');
			$pc = (!substr($postcode,0,1)) ? substr($postcode,-3) : $postcode;

			foreach($australia as $state => $postcodes) {
				if (in_array($pc,$postcodes) && in_array($state,$deliveryStates)) die('1');
			}
			die('0');
		}
		die('0');		
	}
}