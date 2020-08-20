<?php
class Allure_PrivateSale_BookViewingController extends Mage_Core_Controller_Front_Action{


    public function registerAction()
    {
        $result = [
            'success' => false
        ];

         if($this->sendEmail())
         {

             $result = [
                 'success' => true
             ];

         }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function sendEmail()
    {

        $data=$this->getRequest()->getPost();

        $this->add_log($data);

        $templateId = $this->helper()->getTemplateId();

        $mailTemplate = Mage::getModel('core/email_template');
        $storeId = Mage::app()->getStore()->getId();
        $senderName = 'book a viewing registration system';
        $senderEmail = 'bookviewing@mariatash.com';


        $sender = array('name' => $senderName,
            'email' => $senderEmail);

        $recieverEmails = $this->helper()->getEmailReceiver();
        $recieverNames="";

        $recipientEmails = explode(',',$recieverEmails);
        $recipientNames = explode(',',$recieverNames);


        $emailTemplateVariables=$data;



        try {
            $mailTemplate
                ->sendTransactional(
                    $templateId,
                    $sender,
                    $recipientEmails, //here comes recipient emails
                    $recipientNames, // here comes recipient names
                    $emailTemplateVariables,
                    $storeId
                );

            if ($mailTemplate->getSentSuccess()) {
                $this->add_log("Email Sent");
                return true;
            }

            return false;
        }
        catch (Exception $e){
            $this->add_log('email exception=>'.$e->getMessage());
        }

    }

    public function add_log($message)
    {
        Mage::log($message,Zend_log::DEBUG,"bookviewing.log",true);
    }
    private function Helper()
    {
        return Mage::helper('privatesale/bookviewing');
    }
}
