<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Manufacturers
{
    private $responseArray = array();
    private $Manufacturers = array();

    public function setStatusCode($StatusCode)
    {
        $this->responseArray['StatusCode'] = $StatusCode?$StatusCode:0;
    }
    public function setStatusMessage($StatusMessage)
    {
        $this->responseArray['StatusMessage'] = $StatusMessage?$StatusMessage:'';
    }
    public function setManufacturers($Manufacturer)
    {
        $this->Manufacturers[] = $Manufacturer?$Manufacturer:'';
    }
    public function getManufacturers()
    {
        $this->responseArray['Manufacturers'] = $this->Manufacturers;
        return $this->responseArray;
    }
}