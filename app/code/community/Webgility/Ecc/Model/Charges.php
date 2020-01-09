<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Charges
{
    private $responseArray = array();

    public function setDiscount($Discount)
    {
        $this->responseArray['Discount'] = $Discount?$Discount:0;
    }
    public function setStoreCredit($StoreCredit)
    {
        $this->responseArray['StoreCredit'] = $StoreCredit?$StoreCredit:0;
    }
    public function setTax($Tax)
    {
        $this->responseArray['Tax'] = $Tax?$Tax:0;
    }
    public function setShipping($Shipping)
    {
        $this->responseArray['Shipping'] = $Shipping?$Shipping:0;
    }
    public function setTotal($Total)
    {
        $this->responseArray['Total'] = $Total?$Total:0;
    }
    public function setSubTotal($SubTotal=0.00)
    {
        $this->responseArray['SubTotal'] = $SubTotal?$SubTotal:0;
    }
    public function getCharges()
    {
        return $this->responseArray;
    }
}