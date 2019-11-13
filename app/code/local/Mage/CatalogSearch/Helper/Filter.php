<?php
class Mage_CatalogSearch_Helper_Filter extends Mage_Core_Helper_Abstract{
    const CATEGORY_VAR_NAME = 'cat';
    const METAL_VAR_NAME = 'metal';
    const PRICE_VAR_NAME = 'prs';
    const GEMS_VAR_NAME = 'gems';
    protected  $_filterApplied;


    public function getCategoryParam()
    {
        return $this->_getRequest()->getParam(self::CATEGORY_VAR_NAME);
    }
    public function getMetalParam()
    {
        return $this->_getRequest()->getParam(self::METAL_VAR_NAME);
    }
    public function getGemsParam()
    {
        return $this->_getRequest()->getParam(self::GEMS_VAR_NAME);
    }
    public function getPriceParam()
    {
        return $this->_getRequest()->getParam(self::PRICE_VAR_NAME);
    }
    public function createGroups($priceArra){
        $range = array();
        $count = count($priceArra);
        if($count > 1):
            $start = 0; $end = round($count / 6);
            for($i=0; $i < 6; $i++){

                $range[$i]['start'] = $priceArra[$start];
                $range[$i]['end'] = $priceArra[$end];
                $start = $end+1;
                $end += $end;

                if($end > $count){
                    $range[$i+1]['start'] = $priceArra[$start];
                    $range[$i+1]['end'] = $priceArra[$count-1];
                    break;
                }
            }

            return $range;
        endif;
        return $priceArra;
    }
    public function isFilterApplied(){
        return $this->_filterApplied;
    }
    public function setFilterApplied($filter = false){
        $this->_filterApplied = $filter;
    }
    public function isFiltersInRequest(){
        return($this->getCategoryParam() || $this->getMetalParam() || $this->getGemsParam() || $this->getPriceParam());
    }

}
