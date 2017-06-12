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
class Ecp_Tattoo_Block_Adminhtml_Consultations_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('see_form', array('legend'=>Mage::helper('ecp_tattoo')->__('Consultation information')));

        $fieldset->addField('artist_name', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Artist'),
            'name'      => 'artist_name',
        ));
                
        $fieldset->addField('from_name', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Name'),
            'name'      => 'from_name',
        ));

        $fieldset->addField('from_email', 'text', array(
            'label'     => Mage::helper('ecp_tattoo')->__('Email'),
            'name'      => 'from_email'
        ));
      
        $fieldset->addField('consultation', 'editor', array(
            'name'      => 'consultation',
            'label'     => Mage::helper('ecp_tattoo')->__('Consultation'),
            'title'     => Mage::helper('ecp_tattoo')->__('Consultation'),           
            'wysiwyg'   => false,
            'style'     => 'height:26em;width:60em;'
        ));
        
    if ( Mage::registry('consultation_data') ) {
          $data = Mage::registry('consultation_data');
          $form->setValues($data->getData());
      }
      return parent::_prepareForm();
  }
  
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}