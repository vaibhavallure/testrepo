<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_CancelItemDetail
{
    private $responseArray = array();
    public function setItemID($ItemID)
    {
        $this->responseArray['ItemID'] = $ItemID ? $ItemID : '';
    }
    public function setItemSku($ItemSku)
    {
        $this->responseArray['SKU'] = $ItemSku ? $ItemSku : '';
    }
    public function setItemName($ItemName)
    {
        $this->responseArray['ProductName'] = $ItemName ? $ItemName : '';
    }
    public function setQtyCancel ($QtyCancel )
    {
        $this->responseArray['QtyCancel'] = $QtyCancel?$QtyCancel : '';
    }
    public function setQtyInOrder ($QtyInOrder)
    {
        $this->responseArray['QtyInOrder'] = $QtyInOrder?$QtyInOrder : '';
    }
    public function setItemPrice ($ItemPrice)
    {
        $this->responseArray['ItemPrice'] = $ItemPrice?$ItemPrice : '';
    }
    public function setPriceCancel ($PriceCancel )
    {
        $this->responseArray['PriceCancel'] = $PriceCancel?$PriceCancel : '';
    }
    public function getCancelItemDetail()
    {
        return $this->responseArray;
    }
}