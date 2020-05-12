<?php
/**
 *
 * @author allure
 *
 */
class Allure_PostLength_Model_Observer extends Varien_Object
{
    /**
     * add post length product.
     */

    private $_cart="";
    private $_itemsAdded=array();

    private $_postLengthAttributeMapping=array(
        "5mm"=>"five_mm_sku",
        "6.5mm"=>"six_point_five_mm_sku",
        "8mm"=>"eight_mm_sku",
        "9.5mm"=>"nine_point_five_mm_sku",
    );



    public function beforeSave($observer){

        $cart=$observer->getCart();
        $this->_cart=$cart;

        if($this->checkIfItemAdded()) {
            $this->addPostLengthItem();
        }
        $this->checkIfItemQtyUpdated();
        $this->checkIfItemRemoved();


    }

    public function afterSave($observer){

        $cart=$observer->getCart();
        $this->_cart=$cart;
        $this->setPostLengthParentItemId();
        $this->session()->setData('quote_items_with_selected_postlength', $this->getQuoteItemsWithSelectedPostLength());

    }


    private function checkIfItemAdded()
    {
        $items["new"]=$this->getQuoteItemsWithSelectedPostLength()["new"];

        $this->_itemsAdded=$items;


        if($items["new"])
            return true;
        else
            return false;

    }
    private function checkIfItemRemoved()
    {
        $newItems=array_keys($this->getQuoteItemsWithSelectedPostLength());
        $oldItems=array_keys($this->session()->getData('quote_items_with_selected_postlength'));


        $this->removePlItem(array_diff($oldItems,$newItems));

    }
    private function checkIfItemQtyUpdated()
    {
        $updatedQtyItems=array();

        $newItems=$this->getQuoteItemsWithSelectedPostLength();
        $oldItems=$this->session()->getData('quote_items_with_selected_postlength');

        $commonItems=array_intersect($newItems,$oldItems);


        foreach ($commonItems as $key=>$cItem)
        {
            if($newItems[$key]['qty']!=$oldItems[$key]['qty'])
            {
                $updatedQtyItems[$key]=$cItem;
            }
        }

        $this->updatePlItemQty($updatedQtyItems);
    }



    private function getQuoteItemsWithSelectedPostLength()
    {
        $quoteItems=array();
        $cart=$this->_cart;

        foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
            $options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
            foreach ($options as $option)
            {
                if(strtolower($option['label'])=="post length")
                {
                    if(!$item->getItemId())
                        $key="new";
                    else
                        $key=$item->getItemId();

                    $quoteItems[$key]['sku']=$item->getSku();
                    $quoteItems[$key]['post_length']=$option['value'];
                    $quoteItems[$key]['qty']=$item->getQty();
                }
            }
        }

        return $quoteItems;
    }

    private function addPostLengthItem()
    {
        $PL_products_info=$this->getPostLengthProductInfo();

        foreach ($PL_products_info as $info)
        {
            $params = array(
                'qty' => $info['qty'],
                'options' => array('parent_sku'=>$info['parent_sku'],'post_length'=>$info['post_length'])
            );

            $this->addProductBySku($info['sku'],$params);
        }

    }
    private function addProductBySku($sku,$params)
    {
        try {

            $product=Mage::getModel("catalog/product")->loadByAttribute('sku',$sku);

            $params = array_merge($params,array(
                'product' => $product->getId(),
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            ));

            $request = new Varien_Object();
            $request->setData($params);

            $this->_cart->addProduct($product, $request);


        }catch (Exception $e)
        {
            Mage::log("Exception",$e->getMessage(),7,'exception.log',true);
        }
    }
    private function getPostLengthProductInfo()
    {
        $newPostLengthItems=array();
        $quoteItems=$this->_itemsAdded;
        $defaultPostLengthKV=$this->getDefaultPostLengthKV();

        foreach ($quoteItems as $info)
        {
            $product=Mage::getModel("catalog/product")->loadByAttribute('sku',$info['sku']);
            $defaultPL=strtolower($defaultPostLengthKV[$product->getDefaultPostlength()]);


            if($product->getDefaultPostlength() && trim($defaultPL)!=trim($info['post_length']))
            {
                if($defaultPL)
                {
                    $productPL=array();
                    $attrPL=$this->_postLengthAttributeMapping[$defaultPL];
                    if($product->getData($attrPL)) {
                        $productPL['sku'] = $product->getData($attrPL);
                        $productPL['qty'] = $info['qty'];
                        $productPL['post_length'] = $info['sku'];
                        $productPL['parent_sku'] = $info['sku'];
                        $newPostLengthItems[] = $productPL;
                    }
                }
            }

            $productPL=array();
            $attrPL=$this->_postLengthAttributeMapping[strtolower($info['post_length'])];

            if($product->getData($attrPL)) {
                $productPL['sku'] = $product->getData($attrPL);
                $productPL['post_length'] = $info['post_length'];
                $productPL['qty'] = $info['qty'];
                $productPL['parent_sku'] = $info['sku'];

                $newPostLengthItems[] = $productPL;
            }
        }

        return $newPostLengthItems;
    }

    private function getDefaultPostLengthKV()
    {
        $atributeCode = 'default_postlength';
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
        $optionsDPL = $attribute->getSource()->getAllOptions();
        $DefaultPostLengthKV=array();

        foreach ($optionsDPL as $options) {
            if(!$options['value'])
                continue;

            $DefaultPostLengthKV[$options['value']]=$options['label'];
        }

        return $DefaultPostLengthKV;
    }
    private function setPostLengthParentItemId()
    {
        foreach ($this->_cart->getQuote()->getAllItems() as $item) {
            foreach ($item->getOptions() as $option) {
                if ($option->getCode() == 'info_buyRequest') {
                    $values = unserialize($option->getValue()); // to array object
                    if ($values && array_key_exists('options', $values)) {
                        if ($values['options'] && array_key_exists('post_length', $values['options']) && array_key_exists('parent_sku', $values['options'])) {
                            $plParentItem=$this->getParentItem($values['options']);
                            $item->setPlParentItem($plParentItem);
                            $item->addOption(array(
                                "code" => "pl_parent_item",
                                "value" =>$plParentItem
                            ));
                            $item->save();
                        }
                    }
                }
            }
        }
        $this->_cart->getQuote()->save();
    }
    private function updatePlItemQty($updatedItem)
    {
        $itemIds=array_keys($updatedItem);
        foreach ($this->_cart->getQuote()->getAllItems() as $item) {
            $plParentItem= $item->getPlParentItem();
            if(in_array($plParentItem,$itemIds))
            {
             $item->setQty($updatedItem[$plParentItem]['qty']);
            }
        }
    }
    private function removePlItem($removedItem)
    {

        foreach ($this->_cart->getQuote()->getAllItems() as $item) {
            $plParentItem= $item->getPlParentItem();
            if(in_array($plParentItem,$removedItem))
            {
                $this->_cart->getQuote()->removeItem($item->getId());
            }
        }
    }
    private function getParentItem($plItemOptions)
    {
        $cart=$this->_cart;

        foreach ($cart->getQuote()->getAllVisibleItems() as $item) {

            if($item->getSku()!=$plItemOptions['parent_sku'])
                continue;

            $options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
            foreach ($options as $option)
            {
                if(strtolower($option['label'])=="post length")
                {
                    if(strtolower($option['value'])==strtolower($plItemOptions['post_length']))
                    {
                        return $item->getId();
                    }
                }
            }
        }

        return "";
    }
    private function session()
    {
        return Mage::getSingleton('core/session');
    }
}

