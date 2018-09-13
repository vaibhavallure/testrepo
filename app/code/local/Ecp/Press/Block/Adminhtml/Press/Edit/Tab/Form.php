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
 * @package     Ecp_Press
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Press
 *
 * @category    Ecp
 * @package     Ecp_Press
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Press_Block_Adminhtml_Press_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('press_form', array('legend'=>Mage::helper('ecp_press')->__('Press information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('ecp_press')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
      $fieldset->addField('original_name', 'text', array(
          'label'     => Mage::helper('ecp_press')->__('Original Article Name '),
          'required'  => FALSE,
          'name'      => 'original_name',
      ));
      
      $fieldset->addField('original_link', 'text', array(
          'label'     => Mage::helper('ecp_press')->__('Original Article Link '),
          'required'  => FALSE,
          'name'      => 'original_link',
      ));
      
      
      $fieldset->addField('publish_date', 'date', array(
          'label'     => Mage::helper('ecp_press')->__('Publish Date'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'publish_date',
          'format' => 'yyyy-MM-dd',
          'image' => $this->getSkinUrl('images/grid-cal.gif'), 
          'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('ecp_press')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ecp_press')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ecp_press')->__('Disabled'),
              ),
          ),
      ));
      
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('ecp_press')->__('Description'),
          'title'     => Mage::helper('ecp_press')->__('Description'),
          'style'     => 'width:250px; height:150px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
      
      
      $fieldset->addField('image_one', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image one'),
          'required'  => true,
          'name'      => 'image_one',
	  ));
      
      
      $fieldset->addField('image_two', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image two'),
          'required'  => false,
          'name'      => 'image_two',
	  ));
      
      $fieldset->addField('image_tree', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image tree'),
          'required'  => false,
          'name'      => 'image_tree',
	  ));
      
      
      $fieldset->addField('image_four', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image four'),
          'required'  => false,
          'name'      => 'image_four',
	  ));
      
      $fieldset->addField('image_five', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Five'),
          'required'  => false,
          'name'      => 'image_five',
      ));
      
      $fieldset->addField('image_six', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Six'),
          'required'  => false,
          'name'      => 'image_six',
      ));
      $fieldset->addField('image_seven', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Seven'),
          'required'  => false,
          'name'      => 'image_seven',
      ));
      $fieldset->addField('image_eight', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Eight'),
          'required'  => false,
          'name'      => 'image_eight',
      ));
      $fieldset->addField('image_nine', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Nine'),
          'required'  => false,
          'name'      => 'image_nine',
      ));
      $fieldset->addField('image_ten', 'image', array(
          'label'     => Mage::helper('ecp_press')->__('Image Ten'),
          'required'  => false,
          'name'      => 'image_ten',
      ));
     
   
      if ( Mage::getSingleton('adminhtml/session')->getPressData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPressData());
          Mage::getSingleton('adminhtml/session')->setPressData(null);
      } elseif ( Mage::registry('press_data') ) {
          //$form->setValues(Mage::registry('press_data')->getData());
          $path = Mage::getBaseUrl('media').'press/';
          $press = Mage::registry('press_data');
          
          $tmp = $press->getImageOne();
          if(is_array($tmp)) $press->setImageOne($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageOne($path.basename($press->getImageOne()));
                  
          $tmp = $press->getImageTwo();
          if(is_array($tmp)) $press->setImageTwo($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageTwo($path.basename($press->getImageTwo()));
		  
		  $tmp = $press->getImageTree();
          if(is_array($tmp)) $press->setImageTree($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageTree($path.basename($press->getImageTree()));
		  
		  $tmp = $press->getImageFour();
          if(is_array($tmp)) $press->setImageFour($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageFour($path.basename($press->getImageFour()));
          
          $tmp = $press->getImageFive();
          if(is_array($tmp)) $press->setImageFive($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageFive($path.basename($press->getImageFive()));
          
          $tmp = $press->getImageSix();
          if(is_array($tmp)) $press->setImageSix($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageSix($path.basename($press->getImageSix()));
          
          $tmp = $press->getImageSeven();
          if(is_array($tmp)) $press->setImageSeven($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageSeven($path.basename($press->getImageSeven()));
          
          
          $tmp = $press->getImageEight();
          if(is_array($tmp)) $press->setImageEight($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageEight($path.basename($press->getImageEight()));
          
          $tmp = $press->getImageNine();
          if(is_array($tmp)) $press->setImageNine($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageNine($path.basename($press->getImageNine()));
          
          $tmp = $press->getImageTen();
          if(is_array($tmp)) $press->setImageTen($path.basename($tmp['value']));
          elseif(!empty($tmp)) $press->setImageTen($path.basename($press->getImageTen()));
          
          
          
          $form->setValues($press->getData());
      }
      return parent::_prepareForm();
  }
}