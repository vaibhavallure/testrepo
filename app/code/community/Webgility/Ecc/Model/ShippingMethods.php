<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_ShippingMethods
{
    private $responseArray = array();
    private $shippingMethods = array();

    public function setStatusCode($StatusCode)
    {
        $this->responseArray['StatusCode'] = $StatusCode?$StatusCode:0;
    }
    public function setStatusMessage($StatusMessage)
    {
        $this->responseArray['StatusMessage'] = $StatusMessage?$StatusMessage:'';
    }
    public function setShippingMethods($shippingMethods)
    {
        $this->shippingMethods[] = $shippingMethods?$shippingMethods:'';
    }
    public function getShippingMethods()
    {
        $this->responseArray['ShippingMethods'] = $this->shippingMethods?$this->shippingMethods:'';
        return $this->responseArray;
    }
}