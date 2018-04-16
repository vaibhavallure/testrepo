<?php
class Teamwork_Common_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::getModel('teamwork_common/observer')->generateProductEcm();
    }
}