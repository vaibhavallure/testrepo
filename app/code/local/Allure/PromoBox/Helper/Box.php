<?php
class Allure_PromoBox_Helper_Box extends Mage_Core_Helper_Abstract
{

    private $_box_positions=array();
    private $_boxes=array();
    private $_box_size="";

    /**
     * Allure_PromoBox_Helper_Box constructor.
     */
    public function __construct()
    {

        $this->_box_size=$this->getBoxSize();
        $this->_box_positions=$this->getBoxPositions();
    }

    /**
     * @param $i
     * print li tag with banner background
     */
    public function generateBox(&$i)
    {

        if(!$this->getCategory()->getStatus())
            return;

        if(!$this->validateDate())
            return;

        if(!$this->moduleEnabled())
            return;


        $limit=Mage::app()->getRequest()->getParam("limit");
        $page=Mage::app()->getRequest()->getParam("p");

        if ($limit && $page)
        {
            if ($i < $this->getI())
                $i = $this->getI() + 1;
            else
                $this->setI($i);
        }else{
            $this->setI($i);
        }

        if(in_array($i,$this->_box_positions))
        {
            $this->_li($i);
            $i=$i+2;
            $this->setI($i);
        }
    }

    /**
     * @return mixed
     */
    private function getCategoryId()
    {
        return Mage::registry("current_category")->getId();
    }

    /**
     * @return array
     */
    private function getBoxPositions()
    {
        $boxPositions=array();
        $pBoxes=array();
        $boxes=$this->getBoxes();

        $count=0;

        foreach ($boxes as $box) {
            $number_of_product_blocks=$box->getRowNumber()*5;


            if($box->getSide()=="right")
                $number_of_product_blocks = $number_of_product_blocks - 1;
            else
                $number_of_product_blocks = $number_of_product_blocks - 4;

            $boxPositions[]=$number_of_product_blocks;

            $pBoxes[$number_of_product_blocks]["banner_id"]=$box->getPromoboxBannerId();

            if($this->_box_size=='two_by_two')
                $pBoxes[$number_of_product_blocks]["position"]="top";

            if($this->_box_size=='two_by_two')
            {
                $boxPositions[]=$number_of_product_blocks+5;
                $pBoxes[$number_of_product_blocks+5]["banner_id"]=$box->getPromoboxBannerId();
                $pBoxes[$number_of_product_blocks+5]["position"]="bottom";
            }

            $count++;
        }
        $this->_boxes=$pBoxes;
        return $boxPositions;
    }

    /**
     * @return mixed
     */
    private function getBoxSize()
    {
        return $this->getCategory()->getSize();
    }

    /**
     * @return Allure_PromoBox_Model_Resource_Category_Collection
     */
    private function getCategory()
    {
        $collection=Mage::getModel("promobox/category")->getCollection()
            ->addFieldToFilter('category_id', array('eq'=>$this->getCategoryId()));

        return $collection->getFirstItem();
    }

    /**
     * @return box collection form filter by category id
     */
    private function getBoxes()
    {
        return Mage::getModel("promobox/box")->getCollection()
            ->addFieldToFilter('promobox_category_id', array('eq'=>$this->getCategory()->getId()))
            ->addFieldToFilter('promobox_banner_id', array('neq'=>0));
    }

    private function getBannerImage($banner_id)
    {
        $image=$this->getBanner($banner_id)->getImage();


        $path=Mage::getBaseUrl()."media/promobox/";

        return $path.$image;
    }
    private function getBannerHtmlBlock($banner_id)
    {
        return $this->getBanner($banner_id)->getHtmlBlock();
    }
    private function getIframeSrc($banner_id)
    {
        return $this->getBanner($banner_id)->getIframeSrc();
    }
    private function getIframe($banner_id)
    {
        $iframe='<iframe src="'.$this->getIframeSrc($banner_id).'" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        return $iframe;
    }

    /**
     * @param $banner_id
     * @return Allure_PromoBox_Model_Banner
     */
    private function getBanner($banner_id)
    {
        return Mage::getModel("promobox/banner")->load($banner_id);
    }

    private function getStyle($i)
    {
        $style='';
        $style.='background-image:url('.$this->getBannerImage($this->_boxes[$i]['banner_id']).');"';

        return $style;
    }
    private function getClass($i)
    {
        $class='';
        $class.='pb-item p-item five-col box-id-'.$i;
        $class.=' '.$this->_box_size;
        $class.=' '.$this->_boxes[$i]['position'];

        return $class;

    }
    private function _li($i)
    {
        $style=$this->getStyle($i);
        $class=$this->getClass($i);
        $htmlBlock="";

        if($this->_box_size=="one_by_two" || ($this->_box_size=="two_by_two" && $this->_boxes[$i]['position']=="top")) {

            if(!empty($this->getIframeSrc($this->_boxes[$i]['banner_id'])) && $this->_box_size=="one_by_two") {
                $htmlBlock = $this->getIframe($this->_boxes[$i]['banner_id']);
                $style="background:none";
            }
            else {
                $htmlBlock = $this->getBannerHtmlBlock($this->_boxes[$i]['banner_id']);
            }
        }
        echo '<li  class="'.$class.'" ><div style="'.$style.'">'.$htmlBlock.'</div></li>';
    }
    private function setI($i)
    {
        $varName="i_".$this->getCategoryId();
        return Mage::getSingleton("core/session")->setData($varName,$i);
    }
    private function getI()
    {
        $varName="i_".$this->getCategoryId();
        return Mage::getSingleton("core/session")->getData($varName);
    }

    private function validateDate()
    {
        $currentdate = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $currentdate = new DateTime($currentdate);


        if(!$this->getCategory()->getStartDate() && !$this->getCategory()->getEndDate()) {
            return true;
        }
        elseif ($this->getCategory()->getStartDate() && !$this->getCategory()->getEndDate())
        {
            $startdate = new DateTime($this->getCategory()->getStartDate());

            if($startdate <= $currentdate)
                return true;
            else
                return false;

        }
        elseif (!$this->getCategory()->getStartDate() && $this->getCategory()->getEndDate())
        {
            $enddate = new DateTime($this->getCategory()->getEndDate());

            if($enddate < $currentdate)
                return false;
            else
                return true;
        }
        elseif ($this->getCategory()->getStartDate() && $this->getCategory()->getEndDate())
        {
            $startdate = new DateTime($this->getCategory()->getStartDate());
            $enddate = new DateTime($this->getCategory()->getEndDate());

            if($startdate <= $currentdate && $currentdate <= $enddate)
                return true;
            else
                return false;
        }

        return true;
    }
    public function moduleEnabled()
    {
        return Mage::getStoreConfig("promobox/module_status/module_enabled");
    }
}