<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Bill
{

    private $responseArray = array();

    public function setCreditCardInfo($CreditCard)
    {
       $this->responseArray['CreditCard'] = $CreditCard?$CreditCard:'';
    }
    public function setPayMethod($PayMethod)
    {
       $this->responseArray['PayMethod'] = $PayMethod?$PayMethod:'';
    }
    public function setTitle($Title)
    {
       $this->responseArray['Title'] = $Title?$Title:'';
    }
    public function setFirstName($FirstName)
    {
        $this->responseArray['FirstName'] = $FirstName?$FirstName:'';
    }
    public function setLastName($LastName)
    {
        $this->responseArray['LastName'] = $LastName?$LastName:'';
    }
    public function setCompanyName($CompanyName)
    {
        $this->responseArray['CompanyName'] = $CompanyName?$CompanyName:'';
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
    public function setZip($Zip)
    {
        $this->responseArray['Zip'] = $Zip?$Zip:'';
    }
    public function setCountry($Country)
    {
        $this->responseArray['Country'] = $Country?$Country:'';
    }
    public function setEmail($Email)
    {
        $this->responseArray['Email'] = $Email?$Email:'';
    }
    public function setPhone($Phone)
    {
        $this->responseArray['Phone'] = $Phone?$Phone:'';
    }
    public function setPONumber($PONumber)
    {
        $this->responseArray['PONumber'] = $PONumber?$PONumber:'';
    }
    public function setGroupName($LastName)
    {
        $this->responseArray['CustomerGroup'] =strlen($LastName)!=0?utf8_encode($LastName):"";
    }
    public function getBill()
    {
        return $this->responseArray;
    }
}