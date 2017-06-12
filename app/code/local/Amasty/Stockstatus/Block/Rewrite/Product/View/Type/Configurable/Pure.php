<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus
*/
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Conf/active'))
{
    class Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Configurable_Pure extends Amasty_Conf_Block_Catalog_Product_View_Type_Configurable {}
} else 
{
    class Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Configurable_Pure extends Mage_Catalog_Block_Product_View_Type_Configurable {}
}