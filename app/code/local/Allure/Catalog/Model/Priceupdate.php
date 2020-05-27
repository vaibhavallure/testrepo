<?php

class Allure_Catalog_Model_Priceupdate
{
    /*
     *General
     *NOT LOGGED IN
     * */
    protected  $groupPricingData = array(
        array('website_id' => 0, 'cust_group' => 0, 'price' => 0),
        array('website_id' => 0, 'cust_group' => 1, 'price' => 0)
    );


    protected $sku_list = array('XTHBF',
        'XTHBF6',
        'XTHD4',
        'XTHD2',
        'ZPBF6R0',
        'XTHBF25D',
        'XTHBF2D',
        'XTHMQD',
        'ZPBF6R3',
        'ZPBF6R15',
        'ZPBF6R0'
        );

    public function executePriceUpdate(){
        $this->writeLog('---------- From Cron --------');
        $totalCount = $totalUpdated = 0;
        foreach ($this->sku_list as $sku)
        {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', $sku)
                ->addAttributeToFilter('type_id', 'configurable');

            $foundCount = $collection->getSize();

            if ($foundCount > 0)
            {
                $childrenCount = 0;
                $product = $collection->getFirstItem();
                $parentId = $product->getId();

                $this->writeLog('Parent SKU: '.$sku);
                $this->writeLog('Parent Id. :'.$parentId);


                /*Set Allowed group to parent*/
                $parentProduct = Mage::getModel('catalog/product')
                    ->load($parentId);

                $priceArray = $parentProduct->getData('group_price');
                echo(count($priceArray));
                if(count($priceArray) < 2) {
                    $priceData = $this->getPriceArray($parentProduct, $this->groupPricingData);
                    var_dump($priceData);
                    $this->savePrice($parentProduct, $priceData);
                    $totalUpdated++;
                }else{
                    $this->writeLog('Ignored for price updation');
                }
                $totalCount++;
                $childrenProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getChildrenIds($parentId);
                $cnt = 0;
                foreach ($childrenProducts as $set)
                {
                    foreach ($set as $child)
                    {
                        /*Set Allowed group to childs*/
                        $childProduct = Mage::getModel('catalog/product')
                            ->load($child);
                        if ($childProduct)
                        {
                            $childPriceArray = $childProduct->getData('group_price');
                            if(count($childPriceArray) < 2) {
                                $priceData = $this->getPriceArray($childProduct, $this->groupPricingData);
                                $this->savePrice($childProduct, $priceData);
                                $totalUpdated++;
                            }else{
                                $this->writeLog('Ignored for price updation');
                            }

                            $totalCount++;
                        }

                        $childrenCount++;
                    }
                }
                $this->writeLog('Total Childrens: ' . $childrenCount);
            } else {
                $this->writeLog('NOT FOUND :' . $sku);
            }
        }
        $this->writeLog('Total Found: ' . $totalCount);
        $this->writeLog('Total Updated: ' . $totalUpdated);

    }

    protected function getPriceArray($product, $priceArray)
    {
        $this->writeLog('--------------------------------------------------------');
        $this->writeLog('Product Id. :' . $product->getId());
        $previousPrices = $product->getData('group_price');
        $this->writeLog('Previous Price: ' . json_encode($previousPrices, true));
        foreach ($previousPrices as $prevPrice) {
            if (isset($prevPrice['cust_group'])) {
                if ($prevPrice['cust_group'] != '0' && $prevPrice['cust_group'] != '1') {
                    array_push($priceArray, $prevPrice);
                }
            }
        }
        $this->writeLog('New Price: ' . json_encode($priceArray, true));

        return $priceArray;
    }

    protected function savePrice($product, $groupPricingData)
    {

        try {
            $product->setData('group_price', $groupPricingData);
            $product->save();
        } catch (Exception $ex) {
            $this->writeLog('Exception While Saving:' . $ex->getMessage());

        }
    }
    public function writeLog($message){
        Mage::log($message, Zend_Log::DEBUG, 'setPostPrice.log', true);
    }

}