<?php
class Allure_PromoBox_Block_Adminhtml_Category_Renderer_Promobox extends Varien_Data_Form_Element_Abstract{

    protected $_element;

    protected $_products_in_a_row=5;

    public function getElementHtml()
    {

        if(Mage::app()->getRequest()->getParam("id"))
        {
            $boxes="";

            foreach ($this->getPromoBoxes() as $box)
            {
                $checkMark=($box->getPromoboxBannerId())?'<div style="background: url('.Mage::getDesign()->getSkinUrl('images/success_msg_icon.gif').') no-repeat 0px 0px;width: 16px;height: 16px;float: left"></div>':'';
                $boxes.="Row Number:".$box->getRowNumber().$checkMark."<br>";
                $boxes.="<select name='box[".$box->getRowNumber()."][promobox_banner_id]' id=banner_".$box->getRowNumber().">";
                $boxes.="<option value=''>Select Banner</option>";
                $boxes.=$this->getBanners($this->getCategoryData("size"),$box->getPromoboxBannerId());
                $boxes.="</select><br>";
                $boxes.="Side:<br> ";

                $selectedR=($box->getSide()=='right')?'selected':'';
                $selectedL=($box->getSide()=='left')?'selected':'';
                ;
                $boxes.="<select name='box[".$box->getRowNumber()."][side]' id=side_".$box->getRowNumber().">";
                $boxes.="<option value='right' ".$selectedR.">Right side of row</option>";
                $boxes.="<option value='left' ".$selectedL.">Left side of row</option>";
                $boxes.="</select><br><br>";
                $boxes.="<input type='hidden' name='box[".$box->getRowNumber()."][row_number]' value=".$box->getRowNumber()." >";
                $boxes.="<input type='hidden' name='box[".$box->getRowNumber()."][id]' value=".$box->getId()." >";
            }

        }

        $html='<div id="promoboxes">'.$boxes.'</div>';
        $html.='<div id="one_by_two" style="display: none">'.$this->getBanners("one_by_two").'</div>';
        $html.='<div id="two_by_two" style="display: none">'.$this->getBanners("two_by_two").'</div>';
        return $html;
    }
    /**
     * @return array
     */
    protected function getCategories()
    {
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('product_count')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq'=>'1'))
            ->load()
            ->toArray();

        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if($this->isCategoryAlreadyUsed($catId))
                continue;


            if ($this->getProductCount($catId)<5)
                continue;

            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level'  =>$category['level'],
                    'value' => $catId
                );
            }
        }
        return $categoryList;
    }

    /**
     * @param $category_id
     * @return bool
     */
    protected function isCategoryAlreadyUsed($category_id)
    {
        $category=  Mage::getModel("promobox/category")->getCollection()
            ->addFieldToFilter('category_id', array('eq'=>$category_id))
            ->addFieldToFilter('id', array('neq'=>Mage::app()->getRequest()->getParam("id")));

        if($category->getSize())
            return true;
        else
            return false;
    }

    /**
     * @param $category_id
     * @return string
     */
    protected function isSelected($category_id)
    {
        $category=  Mage::getModel("promobox/category")->getCollection()
            ->addFieldToFilter('category_id', array('eq'=>$category_id))
            ->addFieldToFilter('id', array('eq'=>Mage::app()->getRequest()->getParam("id")));

        if($category->getSize())
            return "selected";
        else
            return "";
    }

    /**
     * @param $category_id
     * @return int
     */
    protected function getProductCount($category_id)
    {
        $cat = Mage::getModel('catalog/category')->load($category_id);
        $productCollection=$cat->getProductCollection()
            ->addAttributeToFilter('status', array('eq'=>'1'));

        return (int)$productCollection->getSize();
    }

    protected function getRowCount($category_id)
    {
        return $row_count=round($this->getProductCount($category_id)/$this->_products_in_a_row);
    }

    protected function getBanners($size,$selected=null)
    {
        $banners=  Mage::getModel("promobox/banner")->getCollection()
            ->addFieldToFilter('size', array('eq'=>$size));

        $html="";
        foreach ($banners as $banner)
        {
            $select=($banner->getId()==$selected)? "selected": "";
            $html.='<option value="'.$banner->getId().'" '.$select.'>'.$banner->getName().'</option>';
        }

        return $html;
    }
    protected function getPromoBoxes()
    {
        $promoboxCategoryId=Mage::app()->getRequest()->getParam("id");
        $boxes=  Mage::getModel("promobox/box")->getCollection()
            ->addFieldToFilter('promobox_category_id', array('eq'=>$promoboxCategoryId));

        return $boxes;
    }
    protected function getCategoryData($key)
    {
        $model=Mage::getModel("promobox/category")->load(Mage::app()->getRequest()->getParam("id"));
        return  $model->getData($key);
    }

}