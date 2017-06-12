<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('restrictcustomergroup_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('restrictcustomergroup')->__('Basic Information'));
  }

  protected function _beforeToHtml()
  {
      $type = $this->getRequest()->getParam('type'); // echo '<pre>';print_r($type);echo'</pre>';
      $_data = Mage::registry('restrictcustomergroup_data')->getData();
      
      if (!empty($_data))
      {
        $type = $_data['form_type'];  
      }
      
      if ($type)
      {
        if ($type == 'basic')
        {    
            $this->addTab('form_section', array(
              'label'     => Mage::helper('restrictcustomergroup')->__('Rule Information'),
              'title'     => Mage::helper('restrictcustomergroup')->__('Rule Information'),
              'content'   => $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_form')->toHtml(),
            ));
           
            $this->addTab('cms_section', array(
                'label'     => Mage::helper('restrictcustomergroup')->__('Restrict Cms Page(s)'),
                'title'     => Mage::helper('restrictcustomergroup')->__('Restrict Cms Page(s)'),
                'content'   => $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_cms')->toHtml(),
            ));
            
            $this->addTab('conditions_section', array(
                  'label'     => $this->__('Conditions'),
                  'title'     => $this->__('Conditions'),
                  'content'   => $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_conditions')->toHtml(),
              ));

           $this->addTab('staticblock_section', array(
                  'label'     => Mage::helper('restrictcustomergroup')->__('Static Blocks'),
                  'url'       => $this->getUrl('*/*/staticBlocks', array('_current' => true)),
                  'class'     => 'ajax',
            ));
        }
        
        if ($type == 'manual')
        {  
            $this->addTab('manual_form_section', array(
                'label'     => Mage::helper('restrictcustomergroup')->__('Rule Information'),
                'title'     => Mage::helper('restrictcustomergroup')->__('Rule Information'),
                'content'   => $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_form')->toHtml(),
            ));
            
            $this->addTab('manual_form_input', array(
                'label'     => Mage::helper('restrictcustomergroup')->__('Manual Redirect Opts.'),
                'title'     => Mage::helper('restrictcustomergroup')->__('Manual Redirect Opts.'),
                'content'   => $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_manual_form')->toHtml(),
            ));
        }
        
      }
      else
      {  
        $this->addTab('set', array(
                'label'     => Mage::helper('restrictcustomergroup')->__('Settings'),
                'content'   => $this->_translateHtml($this->getLayout()
                    ->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_type')->toHtml()),
                'active'    => true
            ));
      }
     
      return parent::_beforeToHtml();
  }
  
  /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
}