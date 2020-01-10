<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Customer
{
    private $responseArray = array();
    public function setCustomerId($CustomerId)
    {
        $this->responseArray['CustomerId'] = $CustomerId?$CustomerId:'';
    }
    public function setFirstName($FirstName)
    {
        $this->responseArray['FirstName'] = $FirstName?$FirstName:'';
    }
    public function setMiddleName($MiddleName)
    {
        $this->responseArray['MiddleName'] = $MiddleName?$MiddleName:'';
    }
    public function setLastName($LastName)
    {
        $this->responseArray['LastName'] = $LastName?$LastName:'';
    }
    public function setCustomerGroup($CustomerGroup)
    {
        $this->responseArray['CustomerGroup'] = $CustomerGroup?$CustomerGroup:'';
    }
    public function setcompany($company)
    {
        $this->responseArray['Company'] = $company?$company:'';
    }
    public function setemail($email)
    {
        $this->responseArray['email'] = $email?$email:'';
    }
    public function setAddress1($Address1)
    {
        $this->responseArray['Address1'] = $Address1?$Address1:'';
    }
    public function setAddress2($Address2)
    {
        $this->responseArray['Address2'] = $Address2?$Address2:'';
    }
    public function setCity($City)
    {
        $this->responseArray['City'] = $City?$City:'';
    }
    public function setState($State)
    {
        $this->responseArray['State'] = $State?$State:'';
    }
    public function setStatus($State)
    {
        $this->responseArray['Status'] = $State?$State:'';
    }
    public function setZip($Zip)
    {
        $this->responseArray['Zip'] = $Zip?$Zip:'';
    }
    public function setCountry($Country)
    {
        $this->responseArray['Country'] = $Country?$Country:'';
    }
    public function setPhone($Phone)
    {
        $this->responseArray['Phone'] = $Phone?$Phone:'';
    }
    public function setCreatedAt($CreatedAt)
    {
        $this->responseArray['CreatedAt'] = $CreatedAt?$CreatedAt:'';
    }
    public function setUpdatedAt($UpdatedAt)
    {
        $this->responseArray['UpdatedAt'] = $UpdatedAt?$UpdatedAt:'';
    }
    public function setLifeTimeSale($LifeTimeSale)
    {
       $this->responseArray['LifeTimeSale'] = $LifeTimeSale?$LifeTimeSale:'';
    }
    public function setAverageSale($AverageSale)
    {
        $this->responseArray['AverageSale'] = $AverageSale?$AverageSale:'';
    }
    public function setGroupName($LastName)
    {
        $this->responseArray['CustomerGroup'] =strlen($LastName)!=0?utf8_encode($LastName):"";
    }
    public function setsubscribedToEmail($subscribedToEmail)
    {
        $this->responseArray['subscribedToEmail'] = $subscribedToEmail?$subscribedToEmail:'false';
    }
    public function getCustomer()
    {
        return $this->responseArray;
    }
}