<?php
class Teamwork_Transfer_Teamworktransfer_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected $_flags = array(
        '*' => array(
            Mage_Core_Controller_Varien_Action::FLAG_NO_CHECK_INSTALLATION => true,
            Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION => true,
            Mage_Core_Controller_Varien_Action::FLAG_NO_PRE_DISPATCH => true,
        )
    );

    public function _construct()
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        set_time_limit(0);
    }

    public function preDispatch()
    {
        Mage::getDesign()->setArea($this->_currentArea);
        $this->getLayout()->setArea($this->_currentArea);
        Mage_Core_Controller_Varien_Action::preDispatch();
        return $this;
    }

    public function getFlag($action, $flag='')
    {
        if (''===$action) {
            $action = $this->getRequest()->getActionName();
        }
        if (''===$flag) {
            return $this->_flags;
        }
        elseif (isset($this->_flags[$action][$flag])) {
            return $this->_flags[$action][$flag];
        }
        elseif (isset($this->_flags['*'][$flag])) {
            return $this->_flags['*'][$flag];
        }
        else {
            return false;
        }
    }

    public function stagingAction()
    {
        $request = $this->getRequest();
        $requestId = $request->getParam('request_id', false);
        if($requestId !== false)
        {
            Mage::getModel("teamwork_transfer/transfer")->run($requestId);
        }
    }

    public function statusAction()
    {
        $request = $this->getRequest();
        $packageId = $request->getParam('package_id', false);

        $return = Mage::getModel("teamwork_transfer/status_chq")->run($packageId);
        if(!empty($return))
        {
            $this->getResponse()->setBody(json_encode($return));
        }
    }

    public function statusomsAction()
    {
        $request = $this->getRequest();
        $statusId = $request->getParam('status_id', false);

        $return = Mage::getModel("teamwork_transfer/status_oms")->run($statusId);
        if(!empty($return))
        {
            $this->getResponse()->setBody(json_encode($return));
        }
    }

    public function getversionAction()
    {
        $version = '<?xml version="1.0" encoding="UTF-8"?>';
        $version .= '<PluginInformation Name="Transfer Teamwork Plug-in for Magento" Version="' . Mage::getConfig()->getNode('modules')->children()->Teamwork_Transfer->version . '"> Description of Plug-in. Plug-in for Magento ' . Mage::getVersion() . ' created by Teamwork Retailer Co. </PluginInformation>';
        $this->getResponse()
             ->clearHeaders()
             ->setHeader('Content-Type', 'text/xml')
             ->setBody($version);

    }

    public function getinventoryandpriceAction()
    {
        $request = $this->getRequest();
        $plu = trim($request->getParam('p', false), " \t\n\r\0\x0B,");
        if(!empty($plu))
        {
            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "select ent.plu, eav.attribute_code, attr.value, inv.qty
            from " . Mage::getSingleton('core/resource')->getTableName('eav_attribute') . " eav
            join " . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_decimal') . " attr on attr.attribute_id=eav.attribute_id
            left join  " . Mage::getSingleton('core/resource')->getTableName('service_items') . "  ent on ent.internal_id=attr.entity_id
            left join  " . Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item') . "  inv on inv.item_id=ent.internal_id
            where eav.backend_model = 'catalog/product_attribute_backend_price'
            and attr.value is not null and ent.plu in ({$plu})";

            $return = array();
            $result = $db->fetchAll($sql);
            if(!empty($result))
            {
                foreach($result as $row)
                {
                    $return[$row['plu']][$row['attribute_code']] = $row['value'];
                    $return[$row['plu']]['qty'] = $row['qty'];
                }
            }

            $this->getResponse()
                 //->clearHeaders()
                 //->setHeader('Content-Type', 'text/xml')
                 ->setBody(json_encode($return));

        }
    }
}