<?php
class IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Images extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid_Renderer_Abstract
{
    protected function Grid()
    {
        $order_id = $this->getOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $order_item_collection = $order->getAllVisibleItems();
        $helper = Mage::helper('catalog/image');
        $count = 0;
       // var_dump($order_item_collection);
        $cell = "<div class='iwd_om_prod_images hide'>";
        foreach($order_item_collection as $item){
          
            $product_id = $item->getProductId();
            $sku = $item->getSku();
          
            try{
                if($product_id)
                    $_product = Mage::getModel('catalog/product')->load($product_id);  
                else {
                    $_product = Mage::getModel('catalog/product');
                    $_product = Mage::getModel('catalog/product')->load($_product->getIdBySku($sku)); }
                $url_min = $helper->init($_product, 'small_image')->resize(50);
                $url_max = $helper->init($_product, 'image')->resize(200);
                if($count%3 == 0){$class = ($count < 3) ? "show":""; $cell .= "<div class='iwd_om_image_row $class'>";}
                $cell .= '<span class="iwd_om_prod_image" data-big-image="'.$url_max.'"><img src="'. $url_min . '"/></span>';
                if($count%3 == 2){$cell .= "</div>";}
                $count++;
            }catch (Exception $e){ }
        }

        $cell .= $count > 3 ? '</div><a class="iwd_order_grid_more show" href="javascript:void(0);" title="' .
            Mage::helper('iwd_ordermanager')->__('Show/hide') . '"></a>' : '</div>';

        return  $cell;
    }

    protected function Export()
    {
        return "";
    }
}
