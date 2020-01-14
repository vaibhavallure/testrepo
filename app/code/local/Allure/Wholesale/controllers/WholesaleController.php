<?php


class Allure_Wholesale_WholesaleController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Wholesale Login'));
        $this->renderLayout();
    }

    public function applicationAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Wholesale Application'));
        $this->renderLayout();
    }

    public function applicationSaveAction()
    {


        $data = $this->getRequest()->getPost();
        $receiver = $this->helper()->getEmailReceiver();
        $html =$this->getHtml($data);

        $mail = Mage::getModel('core/email');
        $mail->setToName('wholesale team');
        $mail->setToEmail($receiver);
        $mail->setBody($html);
        $mail->setSubject(' Application for Wholesale Account from ' . $data["firstname"] . ' ' . $data["lastname"]);
        $mail->setFromEmail('wholesale-application@mariatash.com');
        $mail->setFromName("wholesale application system");
        $mail->setType('html');

        try {
            $mail->send();
            $result['success'] = true;
        } catch (Exception $error) {
            $result['error'] = $error->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    private function helper()
    {
        return Mage::helper("wholesale");
    }
    private function getHtml($data)
    {
        $html='<style> th{text-align:left}</style>
<table>
<tr>
<th>Name</th>
<td>'.$data["firstname"].' '.$data["lastname"].'</td>
</tr>
<tr>
<th>Company Name</th>
<td>'.$data["company_name"].'</td>
</tr>
<tr>
<th>Email Address</th>
<td>'.$data["email"].'</td>
</tr>
<tr>
<th>Telephone Number</th>
<td>'.$data["phone"].'</td>
</tr>
<tr>
<th>Company Wedsite</th>
<td>'.$data["company_website"].'</td>
</tr>
<tr>
<th>Message</th>
<td>'.$data["message"].'</td>
</tr>
<tr>
</table>
';
        return $html;

    }

}
