<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Order
{
    private $Order = array();

    public function setOrderId($OrderId)
    {
        $this->Order['OrderId'] = $OrderId?$OrderId:'';
    }
    public function setTitle($Title)
    {
        $this->Order['Title'] =$Title?$Title:'';
    }
    public function setFirstName($FirstName)
    {
        $this->Order['FirstName'] =$FirstName?$FirstName:'';
    }
    public function setLastName($LastName)
    {
        $this->Order['LastName'] =$LastName?$LastName:'';
    }
    public function setDate($Date)
    {
        $this->Order['Date'] =$Date?$Date:'';
    }
    public function setTime($Time)
    {
        $this->Order['Time'] =$Time?$Time:'';
    }
    public function setStoreID($StoreID)
    {
        $this->Order['StoreID'] =$StoreID?$StoreID:'';
    }
    public function setStoreName($StoreName)
    {
        $this->Order['StoreName'] =$StoreName?$StoreName:'';
    }
    public function setCurrency($Currency)
    {
        $this->Order['Currency'] =$Currency?$Currency:'';
    }
    public function setWeight_Symbol($Weight_Symbol)
    {
        $this->Order['Weight_Symbol'] =$Weight_Symbol?$Weight_Symbol:'';
    }
    public function setWeight_Symbol_Grams($Weight_Symbol_Grams)
    {
        $this->Order['Weight_Symbol_Grams'] =$Weight_Symbol_Grams?$Weight_Symbol_Grams:'';
    }
    public function setCustomerId($CustomerId)
    {
        $this->Order['CustomerId'] =$CustomerId?$CustomerId:'';
    }
    public function setComment($Comment)
    {
        $this->Order['Comment'] =$Comment?$Comment:'';
    }
    public function setStatus($Status)
    {
        $this->Order['Status'] =$Status?$Status:'';
    }
    public function setNotes($Notes)
    {
        $this->Order['Notes'] =$Notes?$Notes:'';
    }
    public function setFax($Fax)
    {
        $this->Order['Fax'] =$Fax?$Fax:'';
    }
    public function setShippedOn($ShippedOn)
    {
        $this->Order['ShippedOn'] = $ShippedOn?$ShippedOn:'';
    }
    public function setShippedVia($ShippedVia)
    {
        $this->Order['ShippedVia'] = $ShippedVia?$ShippedVia:'';
    }
    public function setOrderItems($OrderItems)
    {
        $this->Order['Items'][] = $OrderItems?$OrderItems:'';
    }
    public function setOrderBillInfo($Bill)
    {
        $this->Order['Bill'] = $Bill?$Bill:'';
    }
    public function setOrderShipInfo($Ship)
    {
        $this->Order['Ship'] = $Ship?$Ship:'';
    }
    public function setOrderChargeInfo($Charges)
    {
        $this->Order['Charges'] = $Charges?$Charges:'';
    }
    public function setSalesRep($SalesRep)
    {
        $this->Order['SalesRep'] = $SalesRep ? $SalesRep : "";
    }
    public function setLastModifiedDate($Date)
    {
        $this->Order['LastModifiedDate'] =$Date?$Date:'';
    }
    public function setOrderNotes($OrderNotes)
    {
        $this->Order['OrderNotes'] =$OrderNotes?$OrderNotes:"";
    }
    public function setOrderStatus($OrderStatus)
    {
        $this->Order['OrderStatus'] =$OrderStatus?$OrderStatus:"";
    }
    public function setIsCreditMemoCreated($IsCreditMemoCreated)
    {
        $this->Order['IsCreditMemoCreated'] =$IsCreditMemoCreated?$IsCreditMemoCreated:"0";
    }
    public function setCreditMemos($CreditMemos)
    {
        $this->Order['CreditMemo'][] = $CreditMemos?$CreditMemos:"";
    }
    public function getOrder()
    {
        return $this->Order;
    }
}