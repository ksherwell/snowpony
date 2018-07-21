<?php

namespace Magesales\AddressValidation\Controller\Ajax;

use Magesales\AddressValidation\Model;
use Magesales\AddressValidation\Model\Validation\Validator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class Validation extends Action
{
    /**
     * @var Model\Google\Validation|Model\Ups\Validation|Model\Usps\Validation
     */
    protected $validator;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Validator $validator,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->validator = $validator->getValidator();
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->setAddressForValidation();

        $this->validator->validate();

        return $this->getJsonResponse();
    }

    protected function setAddressForValidation()
    {
        $request = $this->getRequest()->getParams();
        $this->validator->setAddressForValidation($request);
    }

    protected function getJsonResponse()
    {
        $response = $this->prepareResponse();

        /* @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($response);
    }

    protected function prepareResponse()
    {
        $response = $this->validator->getValidationResponse()->toDataObject();
        if ($response->getIsValid()) {
            return $response;
        }

        return $this->appendModalToResponse($response);
    }

    protected function appendModalToResponse($response)
    {
        $resultPage = $this->resultPageFactory->create();

        /* @var $block \Magesales\AddressValidation\Block\Frontend\Form */
        $block = $resultPage->getLayout()->getBlock('AddressValidationForm');
        if (!empty($block)) {
            $validationResponse = $this->validator->getValidationResponse();
            $content = $block
                ->setValidationResponse($validationResponse)
                ->toHtml();

            $response->setModalContent($content);
        }

        return $response;
    }
}
