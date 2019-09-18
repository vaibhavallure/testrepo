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





    /*new functionality ------------------------------------------------*/
    public function getPopupAction()
    {
        $data=array();
        $data['name']=$this->getCeleb()->getCelebrityName();
        $data['des']=$this->getCeleb()->getDescription();
        $i=0;
        foreach ($this->getCelebrityOutfits() as $outfit) {
             $data["outfit"][$i]['id']=$outfit->getId();
             $data["outfit"][$i]['img']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'celebrities' . DS . $outfit->getOutfitImage();
             $data["outfit"][$i]['product']=$this->getAllOutfitProductsArray($outfit);
      $i++;
         }
         echo json_encode($data);
    }
    public function getCelebrityOutfits() {
        $celebrityOutfit = Mage::getModel('ecp_celebrities/outfits')->getCollection()
            ->addFieldToFilter('celebrity_id', $this->getCelebId())
            ->addFieldToFilter('status',1);
        return $celebrityOutfit;
    }
    public function getAllOutfitProducts($outfit) {
        return Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('size_sample')
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', explode(',', $outfit->getRelatedProducts()))
            ->addAttributeToFilter('visibility' , array('neq'=>Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
    }

    public function getAllOutfitProductsArray($outfit)
    {
        $i=0;
        foreach ($this->getAllOutfitProducts($outfit) as $product)
        {
           $data[$i]["id"]=$product->getId();
           $data[$i]["name"]=$product->getName();
           $i++;
        }

        return $data;
    }

    public function getCelebId()
    {
        return $this->getRequest()->getParam("id");
    }
    public function getCeleb()
    {
        return Mage::getModel('ecp_celebrities/celebrities')->load($this->getCelebId());
    }
}