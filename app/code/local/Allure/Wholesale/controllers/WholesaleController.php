<?php


class Allure_Wholesale_WholesaleController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setTitle($this->__('Wholesale Login'));
            $this->renderLayout();
        }else{
            $myaccountURL= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'customer/account/';
            Mage::app()->getResponse()->setRedirect($myaccountURL);
        }
    }

    public function applicationAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Wholesale Application'));
        $this->renderLayout();
    }

    public function applicationSaveAction()
    {

        Mage::getSingleton("core/session")->setData('application_data', "");

        $data = $this->getRequest()->getPost();


        if($this->checkEmailIfAlreadyRegisteredAsWholesale())
        {
            Mage::getSingleton("core/session")->addError("Email Id Already Registered As Wholesale User");
            Mage::register('applicationData', $this->getRequest()->getPost());
            Mage::getSingleton("core/session")->setData('application_data', $data);
            $this->_redirectReferer();
            return;
        }

        $files=$this->imageUpload();

        if(!count($files))
        {
            Mage::getSingleton("core/session")->addError("Image Upload Issue");
            Mage::register('applicationData', $this->getRequest()->getPost());
            Mage::getSingleton("core/session")->setData('application_data', $data);
            $this->_redirectReferer();
            return;
        }

        if(!$this->sendEmail($files))
        {

            Mage::getSingleton("core/session")->addError("Something Went Wrong Please Try Again");
            Mage::register('applicationData', $this->getRequest()->getPost());
            Mage::getSingleton("core/session")->setData('application_data', $data);
            $this->_redirectReferer();
            return;
        }

        Mage::getSingleton("core/session")->addSuccess("Your Application Has Been Submitted Successfully.");
        $this->_redirectReferer();

    }

    private function imageUpload()
    {
        $i=0;
        $path = Mage::getBaseDir('media') . DS . "customer" . DS;
        $files=array();

        foreach ($_FILES['store_image']['name'] as $key => $image) {
            try {
                $uploader = new Mage_Core_Model_File_Uploader(
                    array('name' => $_FILES['store_image']['name'][$i],
                        'type' => $_FILES['store_image']['type'][$i],
                        'tmp_name' => $_FILES['store_image']['tmp_name'][$i],
                        'error' => $_FILES['store_image']['error'][$i],
                        'size' => $_FILES['store_image']['size'][$i]));
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $destFile = $path . $image;

                $filename = $uploader->save($path, $image);
                $uploader->save($path, $filename);

                $files[]=$filename['path']."".$filename['file'];

            }catch (Exception $e)
            {
                var_dump($e->getMessage());
            }
            $i++ ;
        }

        return $files;

    }

    private function helper()
    {
        return Mage::helper("wholesale");
    }


    private function checkEmailIfAlreadyRegisteredAsWholesale()
    {
        $email = $this->getRequest()->getPost("email");
        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('email', $email )
            ->addAttributeToFilter('group_id', 2 );

        return $customer->getSize();
    }

    public function sendEmail($files)
    {

        $data=$this->getRequest()->getPost();

        $this->add_log($data);

        $templateId = $this->helper()->getTemplateId();

        $mailTemplate = Mage::getModel('core/email_template');
        $storeId = Mage::app()->getStore()->getId();
        $senderName = 'wholesale application system';
        $senderEmail = 'wholesale-application@mariatash.com';


        $sender = array('name' => $senderName,
            'email' => $senderEmail);

        $recieverEmails = $this->helper()->getEmailReceiver();
        $recieverNames="";

        $recipientEmails = explode(',',$recieverEmails);
        $recipientNames = explode(',',$recieverNames);


        $emailTemplateVariables=$data;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $name = pathinfo($file)['basename'];
                $mailTemplate->getMail()->createAttachment(
                    file_get_contents($file),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    $name
                );
            }
        }


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
        Mage::log($message,Zend_log::DEBUG,"wholesale_system.log",true);
        Mage::log($message,Zend_log::DEBUG,"adi.log",true);
    }


}
