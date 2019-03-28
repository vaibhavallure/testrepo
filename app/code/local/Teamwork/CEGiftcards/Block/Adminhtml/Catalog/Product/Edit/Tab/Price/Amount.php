<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_Amount
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('teamwork_cegiftcards/catalog/product/edit/price/amount.phtml');
    }

    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();
        $this->unsetChild('add_button');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('catalog')->__('Add Amount'),
                'onclick' => 'return amountControl.addItem()',
                'class' => 'add'
            ));
        $button->setName('add_amount_item_button');

        $this->setChild('add_button', $button);
        return $result;
    }

    public function getValues()
    {
        $result = array();
        $product = $this->getProduct();
        $amounts = $product->getTypeInstance(true)->getLoadAmounts($product);
        foreach($amounts as $amount) {
            $result[] = array('price' => $amount);
        }
        return $result;
    }

}
