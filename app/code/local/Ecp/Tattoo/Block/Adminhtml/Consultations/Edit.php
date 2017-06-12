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
class Ecp_Tattoo_Block_Adminhtml_Consultations_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_tattoo';
        $this->_controller = 'adminhtml_consultations';
        
        
        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('back');
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "setLocation('".$this->getUrl('*/adminhtml_tattoo/consultations')."')",
            'class'     => 'back',
        ), -100);
   
    }

    public function getHeaderText()
    {

        return Mage::helper('ecp_tattoo')->__("Consultation");

    }
    
    protected function _prepareLayout() {
        
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->setChild('form', $this->getLayout()->createBlock('ecp_tattoo/adminhtml_consultations_edit_tab_form'));
    }
}