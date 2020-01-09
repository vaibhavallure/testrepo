<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_CreditCard
{

	private $responseArray = array();

    public function setCreditCardType($CreditCardType)
    {
        $this->responseArray['CreditCardType'] = $CreditCardType?$CreditCardType:'';
    }
    public function setCreditCardCharge($CreditCardCharge)
    {
        $this->responseArray['CreditCardCharge'] = $CreditCardCharge?$CreditCardCharge:'';
    }
    public function setExpirationDate($ExpirationDate)
    {
       $this->responseArray['ExpirationDate'] = $ExpirationDate?$ExpirationDate:'';
    }
    public function setCreditCardName($CreditCardName)
    {
       $this->responseArray['CreditCardName'] = $CreditCardName?$CreditCardName:'';
    }
    public function setCreditCardNumber($CreditCardNumber)
    {
        $this->responseArray['CreditCardNumber'] = $CreditCardNumber?$CreditCardNumber:'';
    }
    public function setCVV2($CVV2)
    {
        $this->responseArray['CVV2'] =$CVV2?$CVV2:'';
    }
    public function setAdvanceInfo($AdvanceInfo)
    {
        $this->responseArray['AdvanceInfo'] = $AdvanceInfo?$AdvanceInfo:'';
    }
    public function setTransactionId($TransactionId)
    {
        $this->responseArray['TransactionId'] =$TransactionId?$TransactionId:'';
    }
    public function getCreditCard()
    {
        return $this->responseArray;
    }
}