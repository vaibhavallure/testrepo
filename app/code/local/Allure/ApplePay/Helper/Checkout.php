<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_Helper_Checkout extends Mage_Core_Helper_Abstract
{

    public function _chargeCard()
    {
        $transRequestXmlStr=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<createTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
	<merchantAuthentication></merchantAuthentication>
	<transactionRequest>
		<transactionType>authCaptureTransaction</transactionType>
		<amount>assignAMOUNT</amount>
		<currencyCode>USD</currencyCode>
		<payment>
			<opaqueData>
				<dataDescriptor>assignDD</dataDescriptor>
				<dataValue>assignDV</dataValue>
			</opaqueData>
		</payment>
		<order>
			<invoiceNumber>INV-12345</invoiceNumber>
			<description>Apple Pay Order</description>
		</order>

		<customer>
			<id>0</id>
		</customer>
		<billTo>
			<firstName>Maria</firstName>
			<lastName>Tash</lastName>
		</billTo>
		<shipTo>
			<firstName>Maria</firstName>
			<lastName>Tash</lastName>
			<company></company>
			<address>653 Broadway</address>
			<city>New York</city>
			<state>NY</state>
			<zip>10012</zip>
			<country>USA</country>
		</shipTo>
	</transactionRequest>
</createTransactionRequest>
XML;

		$lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();

		if (!$lastOrderId) return false;

		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();

		$order = Mage::getModel('sales/order')->load($orderId);

		$requestData = array(
			"createTransactionRequest" => array(
				"merchantAuthentication" =>  array(
					"name" =>  "API_LOGIN_ID",
					"transactionKey" =>  "API_TRANSACTION_KEY"
				),
				//"refId" =>  "123456",
				"transactionRequest" =>  array(
					"transactionType" =>  "authCaptureTransaction",
					"amount" =>  $_POST['amount'],
					"payment" =>  array(
						// "creditCard" =>  array(
						// 	"cardNumber" =>  "5424000000000015",
						// 	"expirationDate" =>  "2020-12",
						// 	"cardCode" =>  "999"
						// )
						"opaqueData" =>  array(
							"dataDescriptor" =>  $_POST['dataDesc'],
							"dataValue" =>  $_POST['dataBinary']
						)
					),
					// "lineItems" =>  array(
					// 	"lineItem" =>  array(
					// 		"itemId" =>  "1",
					// 		"name" =>  "vase",
					// 		"description" =>  "Cannes logo",
					// 		"quantity" =>  "18",
					// 		"unitPrice" =>  "45.00"
					// 	)
					// ),
					// "tax" =>  array(
					// 	"amount" =>  "4.26",
					// 	"name" =>  "level2 tax name",
					// 	"description" =>  "level2 tax"
					// ),
					// "duty" =>  array(
					// 	"amount" =>  "8.55",
					// 	"name" =>  "duty name",
					// 	"description" =>  "duty description"
					// ),
					// "shipping" =>  array(
					// 	"amount" =>  "4.26",
					// 	"name" =>  "level2 tax name",
					// 	"description" =>  "level2 tax"
					// ),
					// "poNumber" =>  "456654",
					"customer" =>  array(
						"id" =>  "99999456654"
					),
					"billTo" =>  array(
						"firstName" =>  "Ellen",
						"lastName" =>  "Johnson",
						"company" =>  "Souveniropolis",
						"address" =>  "14 Main Street",
						"city" =>  "Pecan Springs",
						"state" =>  "TX",
						"zip" =>  "44628",
						"country" =>  "USA"
					),
					"shipTo" =>  array(
						"firstName" =>  "China",
						"lastName" =>  "Bayles",
						"company" =>  "Thyme for Tea",
						"address" =>  "12 Main Street",
						"city" =>  "Pecan Springs",
						"state" =>  "TX",
						"zip" =>  "44628",
						"country" =>  "USA"
					)
				)
			)
		);

        $transRequestXml = new SimpleXMLElement($transRequestXmlStr);

        $loginId = 'venus12';
        $transactionKey = '5s8UVJ42HUhj6u9k';

        $transRequestXml->merchantAuthentication->addChild('name',$loginId);
        $transRequestXml->merchantAuthentication->addChild('transactionKey',$transactionKey);

        $transRequestXml->transactionRequest->amount = $_POST['amount'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataDescriptor=$_POST['dataDesc'];
        $transRequestXml->transactionRequest->payment->opaqueData->dataValue=$_POST['dataBinary'];

		$transRequestXml->transactionRequest->order->invoiceNumber = $lastOrderId;

		$transRequestXml->transactionRequest->customer->id = (int) $order->getCustomerId();

		if ($billingAddress = $order->getBillingAddress()) {
			$transRequestXml->transactionRequest->billTo->firstName = $billingAddress->getFirstname();
			$transRequestXml->transactionRequest->billTo->lastName = $billingAddress->getLastname();
			//$transRequestXml->transactionRequest->billTo->company = $billingAddress->getCompany();
			//$transRequestXml->transactionRequest->billTo->address = $billingAddress->getStreetFull();
			//$transRequestXml->transactionRequest->billTo->city = $billingAddress->getCity();
			//$transRequestXml->transactionRequest->billTo->state = $billingAddress->getRegion();
			//$transRequestXml->transactionRequest->billTo->zip = $billingAddress->getPostcode();
			//$transRequestXml->transactionRequest->billTo->country = $billingAddress->getCountry();
		}

		if ($shippingAddress = $order->getShippingAddress()) {
			$transRequestXml->transactionRequest->shipTo->firstName = $shippingAddress->getFirstname();
			$transRequestXml->transactionRequest->shipTo->lastName = $shippingAddress->getLastname();
			$transRequestXml->transactionRequest->shipTo->company = $shippingAddress->getCompany();
			$transRequestXml->transactionRequest->shipTo->address = $shippingAddress->getStreetFull();
			$transRequestXml->transactionRequest->shipTo->city = $shippingAddress->getCity();
			$transRequestXml->transactionRequest->shipTo->state = $shippingAddress->getRegionCode();
			$transRequestXml->transactionRequest->shipTo->zip = $shippingAddress->getPostcode();
			$transRequestXml->transactionRequest->shipTo->country = $shippingAddress->getCountry();
		}

        if ($_POST['dataDesc'] === 'COMMON.VCO.ONLINE.PAYMENT') {
            $transRequestXml->transactionRequest->addChild('callId',$_POST['callId']);
        }

        if (isset($_POST['paIndicator'])){
            $transRequestXml->transactionRequest->addChild('cardholderAuthentication');
            $transRequestXml->transactionRequest->addChild('authenticationIndicator',$_POST['paIndicator']);
            $transRequestXml->transactionRequest->addChild('cardholderAuthenticationValue',$_POST['paValue']);
        }

        $url="https://api.authorize.net/xml/v1/request.api";

        Mage::log("REQUEST: ".$transRequestXml->asXML(),Zend_Log::DEBUG, 'applepay.log', true);

        //print_r($transRequestXml->asXML());

        try {
            $ch = curl_init();
            if (FALSE === $ch) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $transRequestXml->asXML());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//for production, set value to true or 1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            $content = curl_exec($ch);

            if (FALSE === $content) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);

            $xmlResult=simplexml_load_string($content);

            $jsonResult=json_encode($xmlResult);

            Mage::log("RESPONSE: ".$jsonResult,Zend_Log::DEBUG, 'applepay.log', true);

            return $jsonResult;

        } catch (Exception $e) {
            Mage::log("ERROR: ".sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),Zend_Log::DEBUG, 'applepay.log', true);
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

        return false;
    }
}
