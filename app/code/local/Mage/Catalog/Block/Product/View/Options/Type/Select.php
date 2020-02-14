<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2019 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product options text type block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Options_Type_Select
    extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    /**
     * Return html for control element
     *
     * @return string
     */
    public function getValuesHtml()
    {
        $_option = $this->getOption();

        //Mage::log(json_encode(debug_backtrace()),Zend_Log::DEBUG,'abc.log',true);

        $category = Mage::registry('current_category');

        $temparray = array();
        if ($category) {
            $lengths = $category->getAssignedLengths();
            $lengths = explode(',', $lengths);

            $titles = mage::helper('allure_category')->getTitles($lengths);
            $defaultLength = $category->getDefaultLength();
            $defaultTitleTxt = mage::helper('allure_category')->getOptionText($defaultLength);

            $enableLength = $category->getEnablePostlengths();

            $isShownPostLength = $this->getIsShowPostLength($category);

            $count = 0 ;//2;
            if ($enableLength) {
                //if($isShownPostLength){
                foreach ($_option->getValues() as $value) {
                    if (in_array($value->getTitle(), $titles)) {
                        if (strtolower(trim($value->getTitle())) == strtolower(trim($defaultTitleTxt))) {
                            $temparray[$count] = $value;//$temparray[1] = $value;
                        } else {
                            $temparray[$count] = $value;
                            //$count ++;
                        }
                        $count ++;
                    }
                }
                ksort($temparray);
                $temparray = array_values($temparray);
                $_option->setValues($temparray);
                //}
            }
        }
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setData(array(
                    'id' => 'select_'.$_option->getId(),
                    'class' => $require.' product-custom-option'
                ));
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options['.$_option->getid().']');

            } else {
                $select->setName('options['.$_option->getid().'][]');
                $select->setClass('multiselect'.$require.' product-custom-option');
            }

            if (! empty($temparray) && $enableLength)
            {

                if($isShownPostLength)
                    $select->addOption('', 'Select Your Post Length', '', $store, false);

            foreach ($temparray as $_value) {
                $priceStr = $this->_formatPrice(array(
                    'is_percent' => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);

                if(trim($defaultTitleTxt) == trim($_value->getTitle()) && $isShownPostLength){
                    $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . '', array(
                        'price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false),
                        "selected" => "selected"
                    ));
                }else{
                    $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . '', array(
                        'price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false),
                    ));
                }
            }
        } else {

            /*code to set default post length--------------------------*/

            /*first option*/
            $category_for_6point5mm_postLength=explode(",",Mage::getStoreConfig("merchandiser/options/first_post_length_option"));

            /*second option*/
            $category_for_5mm_postLength=explode(",",Mage::getStoreConfig("merchandiser/options/second_post_length_option"));


            $firstMatchingOptions=array_intersect($category_for_6point5mm_postLength,$this->getProduct()->getCategoryIds());
            $secondMatchingOptions=array_intersect($category_for_5mm_postLength,$this->getProduct()->getCategoryIds());

            /* Mage::log($firstMatchingOptions, Zend_Log::DEBUG, "adi.log", true);
             Mage::log($secondMatchingOptions, Zend_Log::DEBUG, "adi.log", true);
             Mage::log($this->getProduct()->getCategoryIds(), Zend_Log::DEBUG, "adi.log", true);*/

            $defaultLengthFlag=false;

            if(count($secondMatchingOptions)) {
                $defaultTitleTxt = "5mm";
                $defaultLengthFlag=true;
            }
            else if(count($firstMatchingOptions)){
                $defaultTitleTxt = "6.5mm";
                $defaultLengthFlag=true;
            }

            if($defaultLengthFlag) {
                $count = 2;

                foreach ($_option->getValues() as $value) {
                    if (strtolower(trim($value->getTitle())) == strtolower(trim($defaultTitleTxt))) {
                        $temparray[1] = $value;
                    } else {
                        $temparray[$count] = $value;
                        $count++;
                    }
                }
                ksort($temparray);
                $temparray = array_values($temparray);
                $_option->setValues($temparray);
            }
            /*-------------------------------------------------------*/

            if (! empty($temparray) && $defaultLengthFlag) {
                $postLengthValues = $temparray;

                if($isShownPostLength)
                    $select->addOption('', 'Select Your Post Length', '', $store, false);
            }
            else {
                $select->addOption('', 'Select Your Post Length', '', $store, false);
                $postLengthValues = $_option->getValues();
            }
            foreach ($postLengthValues as $_value) {
                $priceStr = $this->_formatPrice(array(
                    'is_percent' => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);

                if(trim($defaultTitleTxt) == trim($_value->getTitle()) && $isShownPostLength) {

                    $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . '', array(
                        'price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false),
                        'selected' => 'selected'));
                }
                else{
                    $select->addOption($_value->getOptionTypeId(), $_value->getTitle() . ' ' . $priceStr . '', array(
                        'price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false)));
                }
            }
        }
        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= 'style="width: 150px;" onchange="opConfig.reloadPrice()"';
        }
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }

if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
|| $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
) {
$selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list">';
$require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
$arraySign = '';
switch ($_option->getType()) {
case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
$type = 'radio';
$class = 'radio';
if (!$_option->getIsRequire()) {
$selectHtml .= '<li><input type="radio" id="options_' . $_option->getId() . '" class="'
. $class . ' product-custom-option" name="options[' . $_option->getId() . ']"'
. ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
. ' value="" checked="checked" /><span class="label"><label for="options_'
. $_option->getId() . '">' . $this->__('None') . '</label></span></li>';
}
break;
case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;

                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent')
                ));

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $selectHtml .= '<li>' . '<input type="' . $type . '" class="' . $class . ' ' . $require
                    . ' product-custom-option"'
                    . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                    . ' name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId()
                    . '_' . $count . '" value="' . $htmlValue . '" ' . $checked . ' price="'
                    . $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false) . '" />'
                    . '<span class="label"><label for="options_' . $_option->getId() . '_' . $count . '">'
                    . $this->escapeHtml($_value->getTitle()) . ' ' . $priceStr . '</label></span>';
                if ($_option->getIsRequire()) {
                    $selectHtml .= '<script type="text/javascript">' . '$(\'options_' . $_option->getId() . '_'
                        . $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';'
                        . '$(\'options_' . $_option->getId() . '_' . $count
                        . '\').callbackFunction = \'validateOptionsCallback\';' . '</script>';
                }
                $selectHtml .= '</li>';
            }
            $selectHtml .= '</ul>';

            return $selectHtml;
        }
    }

    private function getIsShowPostLength($_category){

    $cat = Mage::getStoreConfig("allure/options/category_to_compare_with");
    $_compare_cat = array();
    array_push($_compare_cat,$cat);
    //if($_category->getId() != $cat):
    $productCategories = $this->getProduct()->getCategoryIds();

    $categories = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToSort('path', 'asc')
        ->addFieldToFilter('is_active', array('eq' => '1'))
        ->addFieldToFilter('entity_id', array('in' => $productCategories))
        ->addAttributeToFilter('level', array('eq','4'))
        ->load()
        ->toArray();

    // Arrange categories in required array

    $categoryList = array();
    foreach ($categories as $catId => $category) {
        if (isset($category['name'])) {
            $categoryList[] = $catId;
        }
    }
    $cat_set = array_intersect($_compare_cat,$categoryList);
    if(count($cat_set) > 0 && count($categoryList) > 1){
        return false;
    }
    return true;
}

}
