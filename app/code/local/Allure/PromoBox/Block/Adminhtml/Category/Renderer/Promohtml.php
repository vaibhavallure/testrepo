<?php
class Allure_PromoBox_Block_Adminhtml_Category_Renderer_Promohtml extends Varien_Data_Form_Element_Abstract{

    protected $_element;

    protected $_products_in_a_row=5;

    public function getElementHtml()
    {
        $html = '<select id="category_id" name="category_id" class="select" onchange="genrateRow()">';

        foreach ($this->getCategories() as $category) {
            $html .= '<option data-row-count="'.$this->getRowCount($category["value"]).'" value="'.$category["value"].'" '.$this->isSelected($category["value"]).'>' . $category["label"] . '</option>';
        }
        $html .='</select>';
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
        return (int)$cat->getProductCount();
    }

    protected function getRowCount($category_id)
    {
        return $row_count=round($this->getProductCount($category_id)/$this->_products_in_a_row);
    }

}