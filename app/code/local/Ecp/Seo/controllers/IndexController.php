<?php
/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {	
        $this->loadLayout();     
        $this->renderLayout();
    }
}