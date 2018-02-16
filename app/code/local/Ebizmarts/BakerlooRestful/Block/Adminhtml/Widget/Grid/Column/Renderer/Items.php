<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_Items extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $payload= json_decode($row->getJsonPayload(),true);
        $error = $row->getFailMessage();
        $storeId=$row->getStoreId();
        
        if (!is_null($payload) and $payload) {
            $products = isset($payload['products']) ? $payload['products'] : array();
            if (!empty($payload['returns'])) {
                $products = array_merge($products, $payload['returns']);
            }
            $collection = $this->_getItemsCollection($products, $error,$storeId);
        }
       // print_r($collection);
        foreach ($collection as $Item){
            echo $Item->getSku()."---".$Item->getPrice();
            echo "<br>";
        }
       
    }
    private function _getItemsCollection($products, $error,$storeId)
    {
        $collection = new Varien_Data_Collection();
        
        foreach ($products as $prod) {
            if ($prod['type'] === 'bundle') {
                $bundleOptions = $prod['bundle_option'];
                
                foreach ($bundleOptions as $option) {
                    $selections = $option['selections'];
                    
                    foreach ($selections as $select) {
                        if (isset($select['selected']) and $select['selected']) {
                            $item = new Varien_Object();
                            $mageProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($select['product_id']);
                            
                            $item->setProductId($select['product_id']);
                            $item->setName($mageProduct->getName());
                            $item->setSku($mageProduct->getSku());
                            $item->setPrice($mageProduct->getPrice());
                            $item->setQty($prod['qty'] * $select['qty']);
                            
                            if ($error) {
                                $pattern = '/'.preg_quote($item->getSku(), '/').'/';
                                
                                if (preg_match($pattern, $error)) {
                                    $item->setError($error);
                                }
                            }
                            
                            $collection->addItem($item);
                        }
                    }
                }
            } elseif ($prod['type'] === 'configurable') {
                $item = new Varien_Object();
                $mageProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($prod['child_id']);
                
                $item->setProductId($mageProduct->getId());
                $item->setName($mageProduct->getName());
                $item->setSku($mageProduct->getSku());
                $item->setPrice($mageProduct->getPrice());
                $item->setQty($prod['qty']);
                
                
                if ($error) {
                    $pattern = '/'.preg_quote($item->getSku(), '/').'/';
                    
                    if (preg_match($pattern, $error)) {
                        $item->setError($error);
                    }
                }
                
                $collection->addItem($item);
            } else {
                $item = new Varien_Object();
                $mageProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($prod['product_id']);
                
                $item->setProductId($prod['product_id']);
                $item->setName($mageProduct->getName());
                $item->setSku($mageProduct->getSku());
                $item->setPrice($mageProduct->getPrice());
                $item->setQty($prod['qty']);
                
                
                if ($error) {
                    $pattern = '/'.preg_quote($item->getSku(), '/').'/';
                    
                    if (preg_match($pattern, $error)) {
                        $item->setError($error);
                    }
                }
                
                $collection->addItem($item);
            }
        }
        
        
        return $collection;
    }
    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        return $row->getSku();
    }
}
