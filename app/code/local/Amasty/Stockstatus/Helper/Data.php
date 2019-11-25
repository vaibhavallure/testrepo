<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Helper_Data extends Mage_Core_Helper_Abstract
{
    const BACKORDER_LABEL = "backorder";

    protected $_escape_stock_msg_array = array("STORECARD", "GIFT");

    protected $_in_stock = "<span class='info-text-two instock-product'>In stock ships within 24 hours (Mon-Fri)</span>";
    protected $_out_stock = "<span class='info-text-three'>The metal color or length combination you selected is out of stock.  Please email cs@mariatash.com for updates.</span>";
    protected $_backorder_with_time = "<span class='info-text-two'>Order now and It will ship <span class='text-lowercase'>%s</span>.</span><br><span class='info-text-three'>The metal color or length combination you selected is backordered.</span> ";
    protected $_backorder_without_time = "<span class='info-text-three'>The metal color or length combination you selected is backordered.</span>";
    protected $_backorder_with_qty = "<span class='info-text-three'>This product is not available in the requested quantity.%s of the items will be backordered.</span>";

    public function show($product)
    {
        return Mage::app()->getLayout()->createBlock('amstockstatus/status')->setProduct($product)->toHtml();
    }

    public function showStockStatus($product, $addAvail = false, $isProductList = false, $show_stock_message = true)
    {
        if ($show_stock_message){
            if($product->isAvailable()){
                $result = $this->__('In stock');
            }
            else{
                $result = $this->__('Out of stock');
            }
        }
        if($isProductList) $result = '';
        $stockItem   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        if($this->getCustomStockStatusText($product) && ( (!Mage::getStoreConfig('amstockstatus/general/displayforoutonly') || !$product->isSaleable()) || ($product->isInStock() && $stockItem->getData('qty') <= Mage::helper('amstockstatus')->getBackorderQnt() ) )){
            if(Mage::getStoreConfig('amstockstatus/general/icononly')){
                if($product->getData('hide_default_stock_status') )
                    $result =  $this->getStatusIconImage($product);
                else
                    $result .=  $this->getStatusIconImage($product);
            }
            else{
                if($product->getData('hide_default_stock_status') )
                    $result =  $this->getStatusIconImage($product) . $this->getCustomStockStatusText($product);
                else
                    $result .=  ' ' . $this->getStatusIconImage($product) . $this->getCustomStockStatusText($product);
            }
        }
        if($addAvail) {
            $result = $this->__('Availability:') . '<span>' . trim ($result) . '</span>';
        }
        if($isProductList) {
            $result = '<p class="availability" style="padding-bottom: 6px;">' . trim ($result) . '</p>';
        }

        return $result;
    }

    public function processViewStockStatus($product, $html)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        if ( (!Mage::getStoreConfig('amstockstatus/general/displayforoutonly') || !$product->isSaleable()) || ($product->isInStock() && $stockItem->getData('qty') <= Mage::helper('amstockstatus')->getBackorderQnt() ) )
        {
            if (Mage::helper('amstockstatus')->getCustomStockStatusText($product))
            {                                                      // leave empty space here
                //            v
                $status = Mage::getStoreConfig('amstockstatus/general/icononly') ? ' ' : Mage::helper('amstockstatus')->getCustomStockStatusText($product);

                if ($status)
                {
                    $status = $this->getStatusIconImage($product) . '<span class="amstockstatus_' . Mage::helper('amstockstatus')->getCustomStockStatusId($product) . '">' . $status . '</span>';
                    $tag  ='<p class="availability';
                    if(strpos($html, $tag) && !strpos($html, $status)){
                        $pattern = "@($tag)(.*?<span>)(.*?)</span>@";
                        if (Mage::getStoreConfig('amstockstatus/general/icononly') || $product->getData('hide_default_stock_status') || (!$product->isConfigurable() && ('bundle' != $product->getTypeId()) && $product->isInStock() && $stockItem->getManageStock() && 0 == $stockItem->getData('qty')))
                        {
                            $html = preg_replace($pattern, '$1$2' . $status . '</span>', $html);
                        }
                        else
                        {
                            $html = preg_replace($pattern, '$1$2$3 ' . $status . '</span>', $html);
                        }
                    }
                    if(!strpos($html, $status)){
                        // regexp
                        $inStock   = Mage::helper('amstockstatus')->__('In stock') . '.?';
                        $outStock  = Mage::helper('amstockstatus')->__('Out of stock') . '.?';
                        $inStock1   = Mage::helper('amstockstatus')->__('In Stock');
                        $outStock1  = Mage::helper('amstockstatus')->__('Out of Stock');

                        if (Mage::getStoreConfig('amstockstatus/general/icononly') || $product->getData('hide_default_stock_status') || (!$product->isConfigurable() && ('bundle' != $product->getTypeId()) && $product->isInStock() && $stockItem->getManageStock() && 0 == $stockItem->getData('qty')))
                        {
                            $html = preg_replace("@($inStock|$outStock|$inStock1|$outStock1)[\s]*<@", '' . $status  . '<', $html);
                        }
                        else
                        {
                            $html = preg_replace("@($inStock|$outStock|$inStock1|$outStock1)[\s]*<@", '$1 ' . $status  . '<', $html);
                        }
                    }
                }
            }
        }
        return $html;
    }

    public function getStatusIconImage($product)
    {
        $iconHtml = '';
        $altText  = '';
        if ($iconUrl = $this->getStatusIconUrl(Mage::helper('amstockstatus')->getCustomStockStatusId($product)))
        {
            if (!Mage::getStoreConfig('amstockstatus/general/alt_text_loggedin') || Mage::getSingleton('customer/session')->isLoggedIn())
            {
                $altText  = Mage::getStoreConfig('amstockstatus/general/alt_text');
            }
            if (false !== strpos($altText, '{qty}'))
            {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $altText   = str_replace('{qty}', intval($stockItem->getData('qty')), $altText);
            }
            $bubble       = Mage::getBaseUrl('js') . 'amasty/amstockstatus/bubble.gif';
            $bubbleFiller = Mage::getBaseUrl('js') . 'amasty/amstockstatus/bubble_filler.gif';
            if ($altText)
            {
                $iconHtml .= <<<INLINECSS
                <style type="text/css">
                /*---------- bubble tooltip -----------*/
                span.tt{
                    position:relative;
                    z-index:950;
                    color:#3CA3FF;
                	font-weight:bold;
                    text-decoration:none;
                }
                span.tt span{ display: none; }
                /*background:; ie hack, something must be changed in a for ie to execute it*/
                span.tt:hover{ z-index:25; color: #aaaaff; background:;}
                span.tt:hover span.tooltip{
                    display:block;
                    position:absolute;
                    top:0px; left:0;
                	padding: 15px 0 0 0;
                	width:200px;
                	color: #3f3f3f;
                	font-size: 12px;
                    text-align: center;
                	filter: alpha(opacity:95);
                	KHTMLOpacity: 0.95;
                	MozOpacity: 0.95;
                	opacity: 0.95;
                }
                span.tt:hover span.top{
                	display: block;
                	padding: 30px 8px 0;
                    background: url($bubble) no-repeat top;
                }
                span.tt:hover span.middle{ /* different middle bg for stretch */
                	display: block;
                	padding: 0 8px;
                	background: url($bubbleFiller) repeat bottom;
                }
                span.tt:hover span.bottom{
                	display: block;
                	padding:3px 8px 10px;
                	color: #548912;
                    background: url($bubble) no-repeat bottom;
                }
                </style>
INLINECSS;
            }
            if ($altText)
            {
                $altText = '<span class="tooltip"><span class="top"></span><span class="middle"><strong>' . $altText . '</strong></span><span class="bottom"></span></span>';
            }
            $iconHtml .= ' <span class="tt"><img src="' . $iconUrl . '" class="amstockstatus_icon" alt="" title="">' . $altText . '</span> ';
        }
        return $iconHtml;
    }

    public function getCustomStockStatusText(Mage_Catalog_Model_Product $product, $qty=0)
    {
        if('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Preorder/active')
            && Mage::helper('ampreorder')->getIsProductPreorder($product)) {
            return Mage::helper('ampreorder')->getProductPreorderNote($product);
        }

        $status      = '';
        $rangeStatus = Mage::getModel('amstockstatus/range');
        $stockItem   = null;

        if(!$product){
            return false;
        }
        $storeId = Mage::app()->getStore()->getStoreId();
        $stockId = $storeId;
        $product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($product->getId());

        $stockItem= Mage::getModel('cataloginventory/stock_item')
            ->loadByProductAndStock($product,$stockId);

        if($stockItem->getIsInStock() == 0 &&
            $stockItem->getUuseConfigBackorders() == 0){
            $status = $this->_out_stock;
        }else if($stockItem->getIsInStock() == 1 && $stockItem->getQty() >= 1){
            $status = "{$this->_in_stock}";
        }else if($stockItem->getIsInStock() == 1 && $stockItem->getQty() <= 0){
            if($product->getBackorderTime()){
                $status = sprintf($this->_backorder_with_time , $product->getBackorderTime());
            }else{
                $status = $this->_backorder_without_time;
            }
        }else if($product->getStockItem()->getManageStock() == 0){
            $status = "{$this->_in_stock}";
        }

        return $status;
    }

    /*
    this function (getCustomOutOfStockStatus) to hide add to cart button if status is out of stock
    Added by aws12.
    */
    public function getCustomOutOfStockStatus(Mage_Catalog_Model_Product $product, $qty=0)
    {
        if('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Preorder/active')
            && Mage::helper('ampreorder')->getIsProductPreorder($product)) {
            return Mage::helper('ampreorder')->getProductPreorderNote($product);
        }
        $status      = 0;
        $rangeStatus = Mage::getModel('amstockstatus/range');
        $stockItem   = null;

        if(!$product)
            return false;

        $storeId = Mage::app()->getStore()->getStoreId();
        $stockId = $storeId;

        $product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($product->getId());

        $stockItem= Mage::getModel('cataloginventory/stock_item')
            ->loadByProductAndStock($product,$stockId);

        if($stockItem->getIsInStock()==0 &&
            $stockItem->getUuseConfigBackorders()==0){
            $status = 1;
        }
        return $status;
    }
    /*
    * end aws12
    */

    public function getCustomStockStatusId(Mage_Catalog_Model_Product $product)
    {
        $statusId    = null;
        $rangeStatus = Mage::getModel('amstockstatus/range');

        if ($product->getData('custom_stock_status_qty_based') || $product->getData('custom_stock_status_quantity_based'))
        {
            $stockItem   = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if(Mage::getStoreConfig('amstockstatus/general/use_range_rules') && $product->getData('custom_stock_status_qty_rule')){
                $rangeStatus->loadByQtyAndRule($stockItem->getData('qty'), $product->getData('custom_stock_status_qty_rule'));
            }
            else{
                $rangeStatus->loadByQty($stockItem->getData('qty'));
            }
        }

        if ($rangeStatus->hasData('status_id'))
        {
            $statusId = $rangeStatus->getData('status_id');
        } elseif (!Mage::getStoreConfig('amstockstatus/general/userangesonly'))
        {
            $statusId = $product->getData('custom_stock_status');
        }

        return $statusId;
    }

    public function getBackorderQnt()
    {
        return 0;
    }

    public function getStatusIconUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR .
            'amstockstatus' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . '/' . 'amstockstatus' . '/' . 'icons' . '/' . $optionId . '.jpg';
        }
        return '';
    }

    public function getStockAlert($product)
    {
        if (!$product->getId() || !Mage::getStoreConfig('amstockstatus/general/stockalert')) // this is the extension's setting.
        {
            return '';
        }

        $tempCurrentProduct = Mage::registry('current_product');
        Mage::unregister('product');
        Mage::unregister('current_product');
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        $alertBlock = Mage::app()->getLayout()->createBlock('productalert/product_view', 'productalert.stock.'.$product->getId());

        if ($alertBlock)
        {
            $alertBlock->setTemplate('productalert/product/view.phtml');
            $alertBlock->prepareStockAlertData();
            $alertBlock->setHtmlClass('alert-stock link-stock-alert');
            //  $alertBlock->setSignupLabel($this->__('The metal color or length combination you selected is out of stock.  Please email cs@mariatash.com for updates.'));
            $html = $alertBlock->toHtml();

            Mage::unregister('product');
            Mage::unregister('current_product');
            Mage::register('current_product', $tempCurrentProduct);
            Mage::register('product', $tempCurrentProduct);

            return $html;
        }

        Mage::unregister('product');
        Mage::unregister('current_product');
        Mage::register('current_product', $tempCurrentProduct);
        Mage::register('product', $tempCurrentProduct);

        return '';
    }

    public function getStatusBySku($sku, $qty=1)
    {
        if (Mage::getStoreConfig('amstockstatus/general/displayinemail'))
        {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if ( !(Mage::getStoreConfig('amstockstatus/general/displayforoutonly') && $product && $product->isSaleable()) || ($product && $product->isInStock() && $stockItem->getData('qty') + $qty <= $this->getBackorderQnt() ) )
            {
                if ($this->getCustomStockStatusText($product))
                {
                    return ' (' . $this->getCustomStockStatusText($product, $qty) . ')';
                }
            }
        }
        return "";
    }


    //Allure custom stock status message
    public function getCustomStockMessage(Mage_Catalog_Model_Product $product){
        $message = "";
        if(!is_null($product->getData('backorder_time')))
            $message = $product->getData('backorder_time');
        return $message;
    }

    //allure order stock status
    public function getStockStatusMessage($sku, $qty=1){
        $message = "";
        if (Mage::getStoreConfig('amstockstatus/general/displayinemail'))
        {
            $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
            $product = Mage::getModel('catalog/product')->load($productId);
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $stockQty = intval($stockItem->getQty());
            $isInStock = $stockItem->getIsInStock();
            $isBackordered = false;
            $backorderedQty = $qty;
            if ($stockQty < $backorderedQty || $stockQty <= 0) {
                $isBackordered = true;
            }

            if ($isBackordered && $product->getStockItem()->getManageStock() == 1) {
                $stockMsg = $this->getCustomStockMessage($product);
                if(!empty($stockMsg))
                    $message = sprintf($this->_backorder_with_time, $stockMsg);
                else
                    $message = sprintf($this->_backorder_with_qty, $backorderedQty);
            } else {
                $message =  $this->_in_stock;
            }
            $message = " (".$message.")";
        }
        return $message;
    }

    public function getEmailStockStatusMessage($item){
        $message = "";
        if (Mage::getStoreConfig('amstockstatus/general/displayinemail'))
        {
            $sku       = $item->getSku();
            $qty       = $item->getQtyOrdered() * 1;
            $storeId   = $item->getStoreId();
            if(empty($storeId)){
                return $message;
            }

            if(!$this->isNotGiftcardProduct($item->getProduct())){
                return $message;
            }

            if($storeId == 1){
                $stockMsg = $item->getBackorderTime();
                if (!empty($stockMsg) && ($stockMsg != self::BACKORDER_LABEL) ) {
                    if(!empty($stockMsg)){
                        $message = sprintf($this->_backorder_with_time, $stockMsg);
                    }else{
                        $message = sprintf($this->_backorder_with_qty, $qty);
                    }
                } else if ($stockMsg == self::BACKORDER_LABEL) {
                    $message = $this->_backorder_without_time;
                }else{
                    $message = $this->_in_stock;
                }
                $message = " (".$message.")";
            }
        }
        return $message;
    }

    /**
     * get standard messages of product stock
     * @return string array
     */
    public function getStockMessageArray(){
        return array(
            "in_stock"                  => $this->_in_stock,
            "out_of_stock"              => $this->_out_stock,
            "backorder_with_time"       => $this->_backorder_with_time,
            "backorder_without_time"    => $this->_backorder_without_time,
            "backorder_with_qty"        => $this->_backorder_with_qty
        );
    }


    /**
     * generate message of product stock qty
     * @return string
     */
    public function getStockMessage($_item){


        $storeId = Mage::app()->getStore()->getStoreId();
        $_product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->loadByAttribute('sku',$_item->getProduct()->getSku());

        $stock = Mage::getModel('cataloginventory/stock_item')
            ->loadByProductAndStock($_product,$storeId);

        $stockQty = intval($stock->getQty());
        $manageStock = $stock->getManageStock();
        $isInStock = $stock->getIsInStock();
        $isBackordered = false;
        $backorderedQty = round($_item->getQty());



        /*
         * set default out of stock message for back order quote item
         * for jira number MT-906
         * start-----------------
         * */
        if(Mage::getSingleton("checkout/session")->getQuote()->getId() && Mage::getSingleton("checkout/session")->getQuote()->getId()!=Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote()->getId())
        {
            if($_item->getQuoteId()==Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote()->getId())
             $isBackordered=true;
        }
        /*end---------------- */


        if ($stockQty < $backorderedQty) {
            $isBackordered = true;
            if ($stockQty >= 0) {
                $backorderedQty = $_item->getQty() - $stockQty;
            }
        }

        $message = "";

        if(!$this->isNotGiftcardProduct($_item->getProduct())){
            return $message;
        }

        if ($isBackordered && $stock->getManageStock() == 1){
            $stockMsg = $this->getCustomStockMessage($_product);
            if(!empty($stockMsg)){
                $message = sprintf($this->_backorder_with_time,$stockMsg);
            }else{
                $message = sprintf($this->_backorder_with_qty,$backorderedQty);
            }
        }else {
            $message = $this->_in_stock;
        }
        return $message;
    }

    /**
     * check product is giftcart or not
     * @return boolean - true|false
     */
    public function isNotGiftcardProduct($product){
        $isNotGiftcard = true;
        $skuSlice = explode("|", $product->getSku());
        $sku = strtoupper(trim($skuSlice[0]));
        if (in_array($sku, $this->_escape_stock_msg_array)) {
            $isNotGiftcard = false;
        }
        return $isNotGiftcard;
    }

    /**
     * check product is gift card or not using sku
     */
    public function isGiftcardProduct($sku){
        $isGiftcard = false;
        $skuSlice = explode("|", $sku);
        $sku = strtoupper(trim($skuSlice[0]));
        if (in_array($sku, $this->_escape_stock_msg_array)) {
            $isGiftcard = true;
        }
        return $isGiftcard;
    }

    /**
     * get order item stock status
     */
    public function getOrderSalesProductStockStatus($item){
        $backTimeMsg = $item->getBackorderTime();
        $stockMsg = "";

        if(!$this->isNotGiftcardProduct($item->getProduct())){
            return $stockMsg;
        }

        if (!empty($backTimeMsg) && $backTimeMsg != self::BACKORDER_LABEL) {
            $stockMsg = sprintf($this->_backorder_with_time, $backTimeMsg);
        } else if ($backTimeMsg == self::BACKORDER_LABEL) {
            $stockMsg = $this->_backorder_without_time;
        } else {
            $stockMsg = " ({$this->_in_stock})";
        }
        return $stockMsg;
    }
}
