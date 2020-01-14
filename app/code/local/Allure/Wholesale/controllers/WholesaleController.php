<?php


class Allure_Wholesale_WholesaleController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function applicationAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function applicationSaveAction()
    {
      $result['success'] = true;
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
       return;

        $data=$this->getRequest()->getPost();

        $templateId = $this->helper()->getTemplateId();

        $emailTemplate = Mage::getModel('core/email_template')->loadByCode($templateId);

        $senderName = "wholesale-application";

        $senderEmail = "wholesale-application@mariatash.com";

        $receiver=$this->helper()->getEmailReceiver();

        $emailTemplateVariables = $data;

        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

        $mail = Mage::getModel('core/email')
            ->setToName($senderName)
            ->setToEmail($receiver)
            ->setBody($processedTemplate)
            ->setSubject('Subject :Wholesale Application')
            ->setFromEmail($senderEmail)
            ->setFromName($senderName)
            ->setType('html');
        try{
            $mail->send();
            $result['success'] = true;
        }
        catch(Exception $error)
        {
            $result['error'] = $error->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    private function helper()
    {
        return Mage::helper("wholesale");
    }

}
