<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_OrderStatus
{
    private $OrderStatus = array();

    public function setOrderStatusID($OrderStatusID)
    {
        $this->OrderStatus['OrderStatusID'] = $OrderStatusID ? $OrderStatusID :"";
    }
    public function setOrderStatusName($OrderStatusName)
    {
        $this->OrderStatus['OrderStatusName'] = $OrderStatusName ? $OrderStatusName :"";
    }
    public function getOrderStatus()
    {
        return $this->OrderStatus;
    }
}