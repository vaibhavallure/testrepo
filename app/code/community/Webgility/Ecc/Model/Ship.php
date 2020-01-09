<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Ship
{
    private $responseArray = array();

    public function setShipMethod($ShipMethod)
    {
        if($ShipMethod!="")
        {
            $ShipMethod= $ShipMethod;
        }
        else
        {
            $ShipMethod='';
        }
        $this->responseArray['ShipMethod'] = $ShipMethod;
    }
    public function setCarrier($Carrier)
    {
        $this->responseArray['Carrier'] = $Carrier?$Carrier:'';
    }
    public function setTrackingNumber($TrackingNumber)
    {
        $this->responseArray['TrackingNumber'] = $TrackingNumber?$TrackingNumber:'';
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
    public function getShip()
    {
        return $this->responseArray;
    }
}