<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {    	    				
	$this->loadLayout();     
	$this->renderLayout();
    }
}