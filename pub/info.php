<?php
$header    = array ();
$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";

$ch = curl_init('https://61.69.252.86:1443/listener/test.asp');

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS,
    'order_id=000000021_1;order_shipping=16.0000;order_shipping_day=;order_shiptitle=;order_shipname=456456;order_shipsurname=456;order_shipcompany=;order_shipphone=56456;order_shipmobilephone=56456;order_shipphoneafterhours=;order_shipaddress1=456456;order_shipaddress2=;order_shipplace=;order_shipdivision=;order_shippostalcode=2508;order_shipcountrycode=AU;order_shipfreightcode=;order_billtitle=;order_billname=ad;order_billsurname=asdasd;order_billcompany=;order_billemail=ilya@sdsdf.kl;order_billphone=45456456;order_billmobilephone=45456456;order_billphoneafterhours=;order_billfax=;order_billaddress1=asdasd;order_billaddress2=;order_billplace=;order_billdivision=;order_billpostalcode=2085;order_billcountrycode=AU;order_hear_about=;order_special_instructions=;order_additional_information=;date_created=2018-07-05 19:33:32;item_qty=1.0000;item_price=110.0000;item_tax=0.0000;item_code=MFRU-BUB-BEL;item_message=;U_TxRefNum=;U_PromoCode=;U_Prepaid=;item_coupon=;item_certificate=;order_subscribe=;order_user_received_hamper=;prod_offset=');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

//$output = curl_exec($ch);
//print_r($output);
//curl_close($ch);
phpinfo();
?>