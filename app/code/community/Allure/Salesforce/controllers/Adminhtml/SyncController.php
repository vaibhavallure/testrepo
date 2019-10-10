<?php

class Allure_Salesforce_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){

            $this->loadLayout();
            $this->_title($this->__("Salesforce Sync"));
            $this->_addContent( $this->getLayout()
               ->createBlock('allure_salesforce/adminhtml_sync')
                ->setTemplate("allure/salesforce/sync.phtml")
               );
            $this->renderLayout();
    }

    public function salesforceSyncAction()
    {
        $formData = $this->getRequest()->getParams();
        //var_dump($formData).die();
        $entityId = $formData["entityId"];
        $objectType = $formData["object_type"];
        $entityArray = explode(',',$entityId);
        $salesfoceModel = Mage::getModel('allure_salesforce/observer_update');
        //echo "<pre>";
        $requestArray = array();
        switch ($objectType) {
            case "account":
                //echo "Your account";
                $requestData = $salesfoceModel->getCustomersUpdateData(null ,$entityArray);
                $requestArray['customers'] = $requestData['customer'];
                $requestArray['contact'] = $requestData['contact'];
                break;
            case "product":
                //echo "Your product";
                $requestData = $salesfoceModel->getProductUpdateData(null ,$entityArray);
                $requestArray['products'] = $requestData;
                break;
            case "order":
                //echo "order";
                $requestData = $salesfoceModel->getUpdatedOrdersData(null ,$entityArray);
                $requestArray['orders'] = $requestData;
                break;
            case "shipment":
                $requestData = $salesfoceModel->getShipmentUpdateData(null ,$entityArray);
                $requestArray['shipment'] = $requestData;
                break;
            case "invoice":
                $requestData = $salesfoceModel->getInvoicesUpdateData(null ,$entityArray);
                $requestArray['invoice'] = $requestData;
                break;
            case "creditmemo":
                $requestData = $salesfoceModel->getCreditMemoUpdateData(null ,$entityArray);
                $requestArray['creditmemo'] = $requestData;
                break;
            case "shipment-track":
                $requestData = $salesfoceModel->getTrackingInfoUpdateData(null ,$entityArray);
                $requestArray['shipment_track'] = $requestData;
                break;
            default:
                return "";
        }
        $salesfoceModel->sendCompositeRequest($requestArray,null);
//        echo "<pre>";print_r($requestArray);
    }
}
