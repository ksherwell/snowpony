<?php

namespace BBApps\SAP\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendToSap implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->getStatus() == 'complete') {
            $items = $order->getItems();
            $i     = 0;
            foreach ($items as $item) {
                if (is_null($item->getParentItemId())) {
                    $i++;
                    $output                       = [];
                    $output['order_id']           = $order->getIncrementId() . '_' . $i;
                    $output['order_shipping']     = $order->getShippingAmount();
                    $output['order_shipping_day'] = '';
                    $addresses                    = $order->getAddresses();
                    foreach ($addresses as $address) {
                        if ($address->getAddressType() == 'billing') {
                            $output['order_billtitle']           = '';
                            $output['order_billname']            = $address->getFirstname();
                            $output['order_billsurname']         = $address->getLastname();
                            $output['order_billcompany']         = $address->getCompany();
                            $output['order_billemail']           = $address->getEmail();
                            $output['order_billphone']           = $address->getTelephone();
                            $output['order_billmobilephone']     = $address->getTelephone();
                            $output['order_billphoneafterhours'] = '';
                            $output['order_billfax']             = $address->getFax();
                            $output['order_billaddress1']        = implode('\n', $address->getStreet());
                            $output['order_billaddress2']        = '';
                            $output['order_billplace']           = '';
                            $output['order_billdivision']        = '';
                            $output['order_billpostalcode']      = $address->getPostcode();
                            $output['order_billcountrycode']     = $address->getCountryId();
                        } elseif ($address->getAddressType() == 'shipping') {
                            $output['order_shiptitle']           = $address->getSuffix();
                            $output['order_shipname']            = $address->getFirstname();
                            $output['order_shipsurname']         = $address->getLastname();
                            $output['order_shipcompany']         = $address->getCompany();
                            $output['order_shipphone']           = $address->getTelephone();
                            $output['order_shipmobilephone']     = $address->getTelephone();
                            $output['order_shipphoneafterhours'] = '';
                            $output['order_shipaddress1']        = implode('\n', $address->getStreet());
                            $output['order_shipaddress2']        = '';
                            $output['order_shipplace']           = '';
                            $output['order_shipdivision']        = '';
                            $output['order_shippostalcode']      = $address->getPostcode();
                            $output['order_shipcountrycode']     = $address->getCountryId();
                            $output['order_shipfreightcode']     = '';
                        }
                    }

                    $output['order_hear_about']             = '';
                    $output['order_special_instructions']   = '';
                    $output['order_additional_information'] = '';
                    $output['date_created']                 = $order->getCreatedAt();
                    $output['item_qty']                     = $item->getQtyOrdered();
                    $output['item_price']                   = $item->getRowTotal();
                    $output['item_tax']                     = $item->getTaxAmount();
                    $output['item_code']                    = $item->getSku();
                    $output['item_message']                 = '';
                    $output['U_TxRefNum']                   = '';
                    $output['U_PromoCode']                  = '';
                    $output['U_Prepaid']                    = '';
                    $output['item_coupon']                  = '';
                    $output['item_certificate']             = '';
                    $output['order_subscribe']              = '';
                    $output['order_user_received_hamper']   = '';
                    $output['prod_offset']                  = '';

                    $outData = [];
                    foreach ($output as $k => $v) {
                        $outData[] = $k . '=' . $v;
                    }

//                    $this->_logger->addInfo(implode(';', $outData));
                    $header    = array ();
                    $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
                    $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";

                    $ch = curl_init('https://61.69.252.86:1443/listener/test.asp');

                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $output);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                    $output = curl_exec($ch);
                    $this->_logger->addInfo(print_r($output,true));
                    curl_close($ch);
                }
            }
        }

        return $this;
    }
}