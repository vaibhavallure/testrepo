<?php 

class Ecp_Category_Block_Adminhtml_Color
    extends Varien_Data_Form_Element_Text
{
/**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $html .= '<script type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'jquery/jquery-1.8.1.min.js"></script>'
            . '<script type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'jquery/jquery.minicolors.js"></script>'
            .'<script type="text/javascript">jQuery(document).ready(function() { jQuery(\'#'.$this->getHtmlId().'\').minicolors({ position: "top"}); });</script>'
            .'<link type="text/css" rel="stylesheet" href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/jquery.minicolors.css" />';

        return $html;
    }
}