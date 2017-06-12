<?php

class Ebizmarts_BakerlooPrices_Model_Adminhtml_Comment
{
    public function getCommentText()
    {
        $comment = "If set to Yes, order totals will be overwritten to match prices submitted by the POS. <br><b>Current version: ";
        $comment .= (string) Mage::getConfig()->getNode('modules/Ebizmarts_BakerlooPrices/version');
        $comment .= "</b></br>";
        return $comment;
    }
}