<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        $id = $this->getRequest()->getParam('id',false);
        
        $celebrity = ($id)
            ? Mage::getModel('ecp_celebrities/celebrities')->load($id)
            : Mage::getModel('ecp_celebrities/celebrities')->getCollection()->getFirstItem();
        
        Mage::register('celebrities',$celebrity);

        $layout = $this->loadLayout(null,true,false);
        /*$layout->getLayout()->getUpdate()->addUpdate("<reference name='content'>
            <block type='ecp_discovernavigation/discoverNavigation' name='discovernavigation' before='celebrity.clientele' template='ecp/discoverNavigation/discoverNavigationMenu.phtml' />
        </reference>");*/
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->renderLayout();
    }
    public function ajaxAction(){
        echo $this->getLayout()->createBlock('Ecp_Celebrities_Block_Celebrities2')->setTemplate('ecp/celebrities/celebrities_ajax.phtml')->toHtml();
    }
    
    public function sendAction(){
        $page = $this->getRequest()->getParam('page');
        $img = $this->getRequest()->getParam('img');
        Mage::register('page',$page);
        Mage::register('img',$img);
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function sendMailAction(){
        try {
            $recipients = $this->getRequest()->getParam('recipients');
            $sender = $this->getRequest()->getParam('sender');
            $page = $this->getRequest()->getParam('page');
            $img = $this->getRequest()->getParam('img');
            $type = $this->getRequest()->getParam('type');

            $mailTemplate = Mage::getModel('core/email_template');            
            $mailTemplate->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => Mage::app()->getStore()->getId()
            ));

            $senderName = $sender['name'];
            $sender['name'] = 'Venus by Maria Tash';
            
            foreach($recipients['email'] as $key=>$name){
                $mailTemplate
                    ->setTemplateSubject('Free Consultation')
                    ->sendTransactional(
                        'celebrities_send_friend',
                        $sender,
                        $recipients['email'][$key],
                        $name,
                        array(
                            'name'          => $name,
                            'email'         => $recipients['email'][$key],
                            'page'          => Mage::getBaseUrl().str_replace(Mage::getBaseUrl(), '', $page),
                            'img'           => $img,
                            'message'       => $sender['message'],
                            'sender_name'   => $senderName,     //$sender['name'],
                            'sender_email'  => $sender['email'],
                            'celebrity_image' => ""
                        )
                    );
            }
            
            //Mage::getSingleton('catalog/session')->addSuccess($this->__('The link to a friend was sent.'));
            $this->_redirectSuccess(Mage::getURL('*/*/send', array('_current' => true, 'confirmation' => 'send', 'type' => $type)));
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('catalog/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('catalog/session')
                ->addException($e, $this->__('Some emails were not sent.'));
        }
    }
}