<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_CompanyInfo
{
    private $responseArray = array();
	
    public function setStatusCode($StatusCode)
    {
        $this->responseArray['StatusCode'] = $StatusCode?$StatusCode:0;
    }
    public function setStatusMessage($StatusMessage)
    {
        $this->responseArray['StatusMessage'] = $StatusMessage?$StatusMessage:'';
    }
    public function setStoreID($StoreID)
    {
        $this->responseArray['StoreID'] = $StoreID ? $StoreID :'';
    }
    public function setStoreName($StoreName)
    {
        $this->responseArray['StoreName'] = $StoreName?$StoreName:'';
    }
    public function setAddress($Address)
    {
        $this->responseArray['Address'] =$Address ? $Address :'';
    }
    public function setcity($city)
    {
        $this->responseArray['city'] = $city ? $city :'';
    }
    public function setState($State)
    {
        $this->responseArray['State'] =$State ? $State : '';
    }
    public function setCountry($Country)
    {
        $this->responseArray['Country'] = $Country ? $Country : '';
    }
    public function setZipcode($Zipcode)
    {
        $this->responseArray['Zipcode'] = $Zipcode ? $Zipcode : '';
    }
    public function setPhone($Phone)
    {
        $this->responseArray['Phone'] =$Phone ? $Phone :'';
    }
    public function setFax($Fax)
    {
        $this->responseArray['Fax'] =$Fax ? $Fax : '';
    }
    public function setWebsite($Website)
    {
         $this->responseArray['Website'] =$Website ? $Website : '';
    }
    public function getCompanyInfo()
    {
         return $this->responseArray;
    }
}