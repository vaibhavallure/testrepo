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
 * @package     Ecp_Celebrities
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Celebrities_Block_Adminhtml_Outfits_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();                
        
        $id = $this->getRequest()->getParam('id');
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_celebrities';
        $this->_controller = 'adminhtml_outfits';
        
        $this->_updateButton('save', 'label', Mage::helper('ecp_celebrities')->__('Save Outfit'));
        $this->_updateButton('save', 'onclick', 'save()');
        $this->_updateButton('delete', 'label', Mage::helper('ecp_celebrities')->__('Delete Outfit'));      
        $this->_updateButton('delete', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/deleteOutfit/outfitId/'.$id) .'\')');
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $products = Mage::registry('celebrities_outfit_data')->getData('related_products');
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('celebrities_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'celebrities_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'celebrities_content');
                }
            }
            
            function save(){
                if(window['productGridJsObject'] != undefined)
                    var selectedProducts = productGridJsObject.massaction.checkedString;
                else
                    var selectedProducts = '$products';
                editForm.submit($('edit_form').action+'productsList/'+selectedProducts);
            }
            
            function saveAndContinueEdit(){
                if(window['productGridJsObject'] != undefined)
                    var selectedProducts = productGridJsObject.massaction.checkedString;
                else
                    var selectedProducts = '$products';
                editForm.submit($('edit_form').action+'productsList/'+selectedProducts+'/back/editOutfit/');
            }
            
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('celebrities_outfit_data') && Mage::registry('celebrities_outfit_data')->getId() ) {
            return Mage::helper('ecp_celebrities')->__("Edit Celebrity Outfit '%s'", $this->htmlEscape(Mage::registry('celebrities_outfit_data')->getCelebrityOutfitId()));
        } else {
            return Mage::helper('ecp_celebrities')->__('Add Outfit');
        }
    }
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}