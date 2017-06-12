<?php
/**
 * Description of Alertstock
 *
 * @category    Ecp
 * @package     Ecp_Alertstock 
 */
class Ecp_Alertstock_IndexController extends Mage_Core_Controller_Front_Action
{
    /*public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }*/
    
    public function testStockAction(){
        Mage::getModel('ecp_alertstock/observer')->process();
        echo 'done';
    }
    
     public function saveEmailForStockAlertAction(){
         
        $response['redirect'] = false;
        
        $session = Mage::getSingleton('catalog/session');
        
        $data = $this->getRequest()->getParams();
        
        $validmail = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
        
        if(empty($data['email']) || !preg_match($validmail,$data['email'])){
            $response['success'] = false;
            $response['message'] = "Please enter a valid email";
            die(Mage::helper('core')->jsonEncode($response));
        }

//        $customer = Mage::getModel('customer/customer');
//  $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());   
//  $customer->loadByEmail($data['email']);
//        
//  if ($customer->getId()) {
//            $response['redirect'] = true;
//            $response['redirect_url'] = Mage::getUrl('customer/account');
//            die(Mage::helper('core')->jsonEncode($response));
//  }
        
        $model = Mage::getModel('ecp_alertstock/alertstock');
        
        $checkForRepeated = $model->getCollection()->addFieldToFilter('email',$data['email'])->addFieldToFilter('product_id',$data['product_id']);
        $var = $checkForRepeated->getFirstItem()->getData();

        if(!empty($var)){
            $response['success'] = true;
            $response['message'] = "Your email has been already saved for notifications";
            if($var['status'] == 1) {
                    $var['status'] = 0;
                    $model->setData($var);
                    $model->save();
            }
            die(Mage::helper('core')->jsonEncode($response));
        }
                
        $model->setData($data);
        try{
            $model->setAddDate(now());
            $model->save();
            
            $response['success'] = true;
            $response['message'] = "We will notify you once this item is back in stock.";
        }catch(Exception $e){
            $session->addError($this->__('Not enough parameters.'));
            $response['success'] = false;
        }
        
        die(Mage::helper('core')->jsonEncode($response));        
    }
}