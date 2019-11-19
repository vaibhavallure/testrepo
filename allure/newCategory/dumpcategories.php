<?php
/**
 * Created by PhpStorm.
 * User: solace
 * Date: 11/11/19
 * Time: 8:35 PM
 */
require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
echo "<pre>";

/** @var array $columns matches atttribute code*/
$columns = array(
    'entity_id',
    'path',
    'name',
    'description',
    'is_anchor',
    "all_children",
    "assigned_lengths",
    "available_sort_by",
    "categorytryon",
    "category_bleed",
    "category_color_breadcrumb",
    "category_color_breadcrumb_cur",
    "category_color_title",
    "category_display",
    "cat_return_policy",
    "children",
    "children_count",
    "custom_apply_to_products",
    "custom_design",
    "custom_design_from",
    "custom_design_to",
    "custom_layout_update",
    "custom_use_parent_settings",
    "default_length",
    "default_sort_by",
    "discover_mt_navigation",
    "display_mode",
    "enable_postlengths",
    "filter_price_range",
    "image",
    "include_in_menu",
    "is_active",
    "is_wholesale",
    "landing_page",
    "level",
    "meta_description",
    "meta_keywords",
    "meta_title",
    "m_show_in_layered_navigation",
    "page_layout",
    "path_in_store",
    "position",
    "remmote_google_taxonomy",
    "seo",
    "separated_jewelry",
    "thumbnail",
    "url_key",
    "url_path",
);

$data = array(
    $columns,
);

$categories = Mage::getResourceModel('catalog/category_collection')
    ->addAttributeToSelect($columns);
//->addIsActiveFilter();

foreach ($categories as $category) {
    $row = array();
    foreach ($columns as $column) {
        $row[] = trim(stripslashes($category->getData($column)));
    }
    $data[] = $row;
}

$path = Mage::getBaseDir('var') . DS . 'export' . DS . 'categories';
$file = 'categories.csv';

// create directory under var/export
$io = new Varien_Io_File();
$io->checkAndCreateFolder($path);

$csv = new Varien_File_Csv();
//$csv->setEnclosure(','); # change enclosure to work with html
$csv->saveData($path . DS. $file, $data);