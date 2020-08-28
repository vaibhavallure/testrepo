<?php
class Allure_PrivateSale_Model_System_Config_Source_Category
{

    // get all Category List

    function getCategoriesTreeView() {
        // Get category collection
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq'=>'1'))
            ->load()
            ->toArray();

        // Arrange categories in required array
        $categoryList = array();
        foreach ($categories as $catId => $category) {
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


    // Return options to system config


    public function toOptionArray()
    {

        $options = array();

        $options[] = array(
            'label' => Mage::helper('privatesale')->__('-- None --'),
            'value' => ''
        );


        $categoriesTreeView = $this->getCategoriesTreeView();

        foreach($categoriesTreeView as $value)
        {
            $catName    = $value['label'];
            $catId      = $value['value'];
            $catLevel    = $value['level'];

            $hyphen = '-';
            for($i=1; $i<$catLevel; $i++){
                $hyphen = $hyphen ."-";
            }

            $catName = $hyphen .$catName;

            $options[] = array(
                'label' => $catName,
                'value' => $catId
            );


        }

        return $options;

    }

}