<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Block_Adminhtml_Customurl_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customurl_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('allure_customurl')->__('Edit Custom Url'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Allure_CustomUrl_Block_Adminhtml_Customurl_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_customurl',
            array(
                'label'   => Mage::helper('allure_customurl')->__('Custom Url'),
                'title'   => Mage::helper('allure_customurl')->__('Custom Url'),
                'content' => $this->getLayout()->createBlock(
                    'allure_customurl/adminhtml_customurl_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve feed entity
     *
     * @access public
     * @return Allure_CustomUrl_Model_Url
     */
    public function getCustomUrl()
    {
        return Mage::registry('current_customurl');
    }
}
