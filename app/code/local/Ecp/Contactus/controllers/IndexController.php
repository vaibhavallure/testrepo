<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 *
 * @category    Ecp
 * @package     Ecp_Contactus
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Contactus
 *
 * @category    Ecp
 * @package     Ecp_Contactus
 * @author      Entrepids Core Team <core@entrepids.com>
 */
header("access-control-allow-origin: http://www.mariatash.com");
class Ecp_Contactus_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function sendMailAction(){

        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }
        $response = array();
        if ($data = $this->getRequest()->getParams()) {
            $response['success'] = false;
            $model = Mage::getModel('ecp_contactus/contactus');
            $model->setData($data);
            try {
                $model->setCreatedTime(now());
                $model->save();

//                $emailTemplate = Mage::getModel('core/email_template')->loadDefault('custom_email_contact_us');
//Mage::log($emailTemplate,null,'milog.log');
                $emailTemplateVariables = array();
                $emailTemplateVariables['firstname'] = $data['firstname'];
                $emailTemplateVariables['lastname'] = $data['lastname'];
                $emailTemplateVariables['email'] = $data['email'];
//                $emailTemplateVariables['subject'] = $data['subject'];
                $emailTemplateVariables['message'] = $data['details'];

//                $emailTemplate->send('felipe.aguilar@entrepids.com','Admin', $emailTemplateVariables);

                $sender = Array('name'  => $data['firstname'],
                  'email' => $data['email']);
                Mage::getModel('core/email_template')
                ->setTemplateSubject($data['subject'])
                ->sendTransactional('custom_email_contact_us', $sender, Mage::getStoreConfig('contacts/email/recipient_email'), null, $emailTemplateVariables, Mage::app()->getStore()->getId());

                $response['success'] = true;
                $response['firstname'] = $this->getRequest()->getParam('firstname');
                die(Mage::helper('core')->jsonEncode($response));

            } catch (Exception $e) {

                die(Mage::helper('core')->jsonEncode($response));
            }
        }
    }
}
