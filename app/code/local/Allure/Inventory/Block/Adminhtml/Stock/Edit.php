<?php


class Allure_Inventory_Block_Adminhtml_Stock_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $source = $this->getRequest()->getParam('id');

        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryplus';
        $this->_controller = 'adminhtml_stock';
        $showcustomer_url = Mage::helper('adminhtml')->getUrl('adminhtml/inp_stock/showcustomer');

        $admin = Mage::getSingleton('admin/session')->getUser();
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('back');

        $stockActions = array();
		
        
        
       
        /* Quick adjust stock button */
        $this->_updateButton('save', 'label', Mage::helper('inventoryplus')->__('Update Stock'));
        $this->_updateButton('save', 'onclick', 'IMmassUpdateStock()');
        $this->_updateButton('save', 'area', 'after_header');
        /* End of Qucik adjust stock button */

        $stockActionsObject = new Varien_Object(array('actions' => $stockActions));
        Mage::dispatchEvent('inventoryplus_inventory_stock_action_buttons', array('stock_actions_object' => $stockActionsObject));

        $this->_generateStockActionButtons($stockActionsObject);

        $this->setTemplate('inventoryplus/stock/content-header.phtml');
        $this->_formScripts[] = "  
              function IMmassUpdateStock(){
                if($('select_warehouse') && $('select_warehouse').getValue() == '0'){
                    alert('" . $this->__('Please select a warehouse to view stock & perform in-grid update.') . "');
                    $('select_warehouse').addClassName('highlight');
                    window.scrollTo(0, 0);
                    return;
                }
                var r=confirm('" . Mage::helper('inventoryplus')->__('Are you sure you want to update Qty. of products?') . "');                    
                if (r==true){
                    editForm.submit($('edit_form').action);
                }
            }      
            
            function IMselectWarehouse() {
                $('select_warehouse').removeClassName('highlight');
            }
            
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }                   
                        
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        return Mage::helper('inventoryplus')->__('Receive Stock');
    }

    /**
     * 
     * @param Varian_Object $actionObject
     */
    protected function _generateStockActionButtons($actionObject) {
        if (!count($actionObject->getData('actions')))
            return $this;
        foreach ($actionObject->getData('actions') as $actionName => $stockAction) {
            $this->_addButton($actionName, $stockAction['params'], 0, $stockAction['position']);
        }
        return $this;
    }

}
