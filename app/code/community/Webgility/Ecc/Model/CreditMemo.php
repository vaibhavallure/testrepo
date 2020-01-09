<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_CreditMemo
{
    private $responseArray = array();
    
    public function setCreditMemoID($CreditMemoID)
    {
        $this->responseArray['CreditMemoID'] = $CreditMemoID ? $CreditMemoID : '';
    }
    public function setCreditMemoDate($CreditMemoDate)
    {
        $this->responseArray['CreditMemoDate'] = $CreditMemoDate ? $CreditMemoDate : '';
    }
    public function setSubtotal($Subtotal)
    {
        $this->responseArray['Subtotal'] = $Subtotal ? $Subtotal : '';
    }
    public function setShippingAndHandling($ShippingAndHandling)
    {
        $this->responseArray['ShippingAndHandling'] = $ShippingAndHandling ? $ShippingAndHandling : '';
    }
    public function setAdjustmentRefund ($AdjustmentRefund )
    {
        $this->responseArray['AdjustmentRefund'] = $AdjustmentRefund ? $AdjustmentRefund : '';
    }
    public function setAdjustmentFee($AdjustmentFee )
    {
        $this->responseArray['AdjustmentFee'] = $AdjustmentFee ? $AdjustmentFee : '';
    }
    public function setCancelItemDetail($CancelItemDetail)
    {
        $this->responseArray['CancelItemDetail'][] = $CancelItemDetail?$CancelItemDetail:'';
    }
    public function getCreditMemo()
    {
        return $this->responseArray;
    }
}