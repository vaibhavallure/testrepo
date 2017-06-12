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
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tattoo
 *
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tattoo_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $id = $this->getRequest()->getParam('id',false);

        $tattooArtist = ($id)
            ? Mage::getModel('ecp_tattoo/tattoo_artist')->load($id)
            : Mage::getModel('ecp_tattoo/tattoo_artist')->getCollection()->getFirstItem();

        if($tattooArtist->getStatus() != 1) {
            $this->_redirect('/');
        }

        Mage::register('tattoo_artist',$tattooArtist);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function contactAction() {

        $data = $this->getRequest()->getParams();
        try {
            // Transactional Email Template's ID
//            $templateId = Mage::getModel('core/email_template')->loadByCode('custom_email_contact_us')->getId();

            // Set sender information          
            $senderName = $this->getRequest()->getParam('name');
            $senderEmail = $this->getRequest()->getParam('email');
            $sender = array(
                'name' => $senderName,
                'email' => $senderEmail
            );

            $tattooer = Mage::getModel('ecp_tattoo/tattoo_artist')->load($this->getRequest()->getParam('tattooer'));

            // Set recepient information
            $recepientEmail = array($tattooer->getEmail(),Mage::getStoreConfig('ecp_tattoo/mails/mail1'),Mage::getStoreConfig('ecp_tattoo/mails/mail2'));
            $recepientName = array($tattooer->getName(),'recipient 1','recipient 2');

            // Get Store ID    
            $store = Mage::app()->getStore()->getId();

            // Set variables that can be used in email template
            $vars = array('message' => $data['message']);

            $translate = Mage::getSingleton('core/translate');
            
            // Send Transactional Email
            Mage::getModel('core/email_template')
                    ->setTemplateSubject('Free Consultation')
                    ->sendTransactional('tattooer_contact_template', $sender, $recepientEmail, $recepientName, $vars, $store);

            $translate->setTranslateInline(true);
            
            $consultations = Mage::getModel('ecp_tattoo/tattoo_consultations');
            $consultations->setArtistName($tattooer->getName());
            $consultations->setFromEmail($data['email']);
            $consultations->setFromName($data['name']);
            $consultations->setConsultation($data['message']);
            $consultations->setCreatedTime(now());
            $consultations->save();
            
            $response['success'] = true;
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        die(json_encode($response));
    }
    
    public function sendAction(){
        Mage::register('page', $this->getRequest()->getParam('page'));
        Mage::register('img', $this->getRequest()->getParam('img'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function sendformAction(){
        /*$id = $this->getRequest()->getParam('id', false);

        $tattooArtist = ($id)
            ? Mage::getModel('ecp_tattoo/tattoo_artist')->load($id)
            : Mage::getModel('ecp_tattoo/tattoo_artist')->getCollection()->getFirstItem();

        Mage::register('tattoo_artist', $tattooArtist);*/
        $this->loadLayout();
        $this->renderLayout();
    }
}