<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

if (Mage::helper("core")->isModuleEnabled("Wyomind_Layer") && class_exists("Wyomind_Layer_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar extends Wyomind_Layer_Block_Catalog_Product_List_Toolbar
    {
        public function getOrderUrl($order, $direction)
        {
            return str_replace(array("\\","<",">"),"",urldecode(parent::getOrderUrl($order, $direction)));
        }
    }
} elseif (Mage::helper("core")->isModuleEnabled("Amasty_Sorting") && class_exists("Amasty_Sorting_Block_Catalog_Product_List_Toolbar")) {
    //Amasty_Sorting before Amasty_Shopby because Sorting already handles the Shopby inheritence if needed.
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar extends Amasty_Sorting_Block_Catalog_Product_List_Toolbar
    {
        public function getOrderUrl($order, $direction)
        {
            return str_replace(array("\\","<",">"),"",urldecode(parent::getOrderUrl($order, $direction)));
        }
    }
} elseif (Mage::helper("core")->isModuleEnabled("Amasty_Shopby") && class_exists("Amasty_Shopby_Block_Catalog_Product_List_Toolbar")) {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar
    {
        public function getOrderUrl($order, $direction)
        {
            return str_replace(array("\\","<",">"),"",urldecode(parent::getOrderUrl($order, $direction)));
        }
    }
} else {
    class Wyomind_Elasticsearch_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
    {
        public function getOrderUrl($order, $direction)
        {
            return str_replace(array("\\","<",">"),"",urldecode(parent::getOrderUrl($order, $direction)));
        }
    }
}