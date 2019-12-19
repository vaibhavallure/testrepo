<?php
class Allure_Category_Model_System_Config_Source_CategoryList
{
// get all Category List

    function getCategoriesTreeView()
    {
// Get category collection
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq' => '1'))
            ->load()
            ->toArray();

// Arrange categories in required array
        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
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
            'label' => '-- None --',
            'value' => ''
        );


        $categoriesTreeView = $this->getCategoriesTreeView();

        foreach ($categoriesTreeView as $value) {
            $catName = $value['label'];
            $catId = $value['value'];
            $catLevel = $value['level'];

            $options[] = array(
                'label' => $catName,
                'value' => $catId
            );


        }

        return $options;

    }
}