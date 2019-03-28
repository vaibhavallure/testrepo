<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Color
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Color
 *
 * @category    Ecp
 * @package     Ecp_Color
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Color_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {   
        //$img_html = $price_html = '';
    	$img_html = '';
        $price_html = array();
        $options = $this->getRequest()->getParam('options');       
        $mode    = $this->getRequest()->getParam('mode');    
        $options = json_decode($options);

        if (!isset($options->code, $options->label, $options->value) || !is_array($options->value)) {
            echo json_encode(array('colors' => '', 'price' => '')); exit;
        }

        $attribute_code = $options->code;
        $attribute_label = $options->label;   
        foreach($options->value as $info){
            if (!isset($info->child_id, $info->$attribute_code)) {
                continue;
            }

            $simple_id = $info->child_id;
            $simpleProduct = Mage::getModel('catalog/product')->load($simple_id);   
            $price = $this->getPriceHtml($simpleProduct, true);
            $optionId = $info->$attribute_code;
            $optionLb = $simpleProduct->getAttributeText($attribute_code);
            $img_html.= '<li id="'. $simple_id .'" style="background: url(\''. Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'amconf/images/' . $optionId . '.jpg'.'\');width: 21px; height: 22px;" 
                class="colorSelector tipped-create" value="'. $optionId .'" 
                title="'.$optionLb.'" >
                <div style="display: none">';
           if($mode != 'grid'){
                 $img_html.=  '<span id="img-'. $simple_id .'"> '. Mage::helper('catalog/image')->init($simpleProduct, 'small_image')->resize(314, 409) .'</span>';
           }else{         
                 $img_html.=  '<span id="img-'. $simple_id .'">' . Mage::helper('catalog/image')->init($simpleProduct, 'small_image')->resize(376, 490) .'</span>'; //resize(234, 305)
           }      
           $img_html.= '<span id="price-'. $simple_id .'">'. $price. '</span></div></li>';
           $price_html[] = $price;
           
        }

        if(empty($price_html)) {
            $simpleProduct = Mage::getModel('catalog/product')->load($options);
            $price_html[] = $this->getPriceHtml($simpleProduct, true);
        }

        echo json_encode(array('colors' => $img_html, 'price' => $price_html[0])); exit;
    }

    public function getPriceHtml($product)
    {
        return Mage::app()->getLayout()->createBlock('Mage_Catalog_Block_Product_Price')
                    ->setTemplate('catalog/product/price.phtml')
                    ->setProduct($product)
                    ->toHtml();   
    }

    public function getPriceAction()
    {
        $productId = $this->getRequest()->getParam('productId');  
        $product = Mage::getModel('catalog/product')->load($productId);

        $block = Mage::app()->getLayout()->createBlock('catalog/product_view_type_' . $product->getTypeId());
        
        $config = '{}';
        
        if ($block) {
	        $block->setProduct($product);
	        $config = $block->getJsonConfig();
        } else {
        	Mage::log('TYPE ELSE:'.$product->getTypeId(),Zend_Log::DEBUG,'jsonconfig.log',true);
        	Mage::log('ID ELSE:'.$product->getId(),Zend_Log::DEBUG,'jsonconfig.log',true);
        }

        Mage::log('TYPE:'.$product->getTypeId(),Zend_Log::DEBUG,'jsonconfig.log',true);
        Mage::log('ID:'.$product->getId(),Zend_Log::DEBUG,'jsonconfig.log',true);
        Mage::log('CLASS:'.get_class($product),Zend_Log::DEBUG,'jsonconfig.log',true);
        
        if ($product->getTypeId() != 'simple' && !empty($block)) {
            $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            $count_superAttribute = count($productAttributeOptions);
            if ($count_superAttribute == 1) {
                $attribute_position = array();
                $attribute_position_1 = array();
                $masterAttributeId = array();

                foreach ($productAttributeOptions as $masterAttribute) {
                    $masterAttributeId['attribute_code'] = $masterAttribute['attribute_code'];
                    $masterAttributeId['attribute_id'] = $masterAttribute['attribute_id'];
                    foreach ($masterAttribute['values'] as $attributeValue) {
                        $attribute_position_1[] = $attributeValue['value_index'];
                        $attribute_position[] = $attributeValue['label'];
                    }
                }

                $select_position = array();
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $count_pr = 0;
                foreach ($childProducts as $childProduct) {
                    $childProductId = $childProduct->getId();
                    $productQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProductId);
                    $productStockStatus = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText('custom_stock_status');
                    $pQty = $productQty->getQty();
                    if ((int)$pQty > 0) {
                        $attribute_id = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['attribute_code']);
                        $attribute_id_position = array_search($attribute_id, $attribute_position);
                        array_push($select_position, array(
                            "attribute_id" => $attribute_id,
                            "attribute_id_position" => $attribute_id_position,
                            "custom_stock_status" => $productStockStatus
                        ));
                        $count_pr++;
                    }
                    if ($count_pr > 0) {
                        usort($select_position,
                            function ($one, $two) {
                                return ($one['attribute_id_position'] > $two['attribute_id_position']) ? 1 : -1;
                            });
                        $select_config = array('sa_count' => 1, 'attribute_value' => $attribute_position_1[$select_position[0]['attribute_id_position']], 'attribute_id' => $masterAttributeId['attribute_id'], 'custom_stock_status' => $select_position[0]['custom_stock_status'], 'count_product' => $count_pr);
                    } else {
                        $select_config = array('sa_count' => 1, 'attribute_value' => $attribute_position_1[0], 'attribute_id' => $masterAttributeId['attribute_id'], 'custom_stock_status' => $select_position[0]['custom_stock_status'], 'count_product' => $count_pr);
                    }
                }

            } elseif ($count_superAttribute == 2) {
                $color_position = array();
                $size_position = array();
                $color_position_1 = array();
                $size_position_1 = array();
                $masterAttributeId = array();

                foreach ($productAttributeOptions as $masterAttribute) {
                    if (strpos($masterAttribute['attribute_code'], 'color')) {
                        $masterAttributeId['color_code'] = $masterAttribute['attribute_code'];
                        $masterAttributeId['color_id'] = $masterAttribute['attribute_id'];
                        foreach ($masterAttribute['values'] as $attributeValue) {
                            $color_position_1[] = $attributeValue['value_index'];
                            $color_position[] = $attributeValue['label'];
                        }
                    } else {
                        $masterAttributeId['size_code'] = $masterAttribute['attribute_code'];
                        $masterAttributeId['size_id'] = $masterAttribute['attribute_id'];
                        foreach ($masterAttribute['values'] as $attributeValue) {
                            $size_position_1[] = $attributeValue['value_index'];
                            $size_position[] = $attributeValue['label'];
                        }
                    }
                }

                $select_position = array();
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $count_pr = 0;
                foreach ($childProducts as $childProduct) {
                    $childProductId = $childProduct->getId();
                    $productQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProductId);
                    $productStockStatus = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText('custom_stock_status');
                    $pQty = $productQty->getQty();
                    if ((int)$pQty > 0) {
                        $color_id = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['color_code']);
                        $size_id = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['size_code']);
                        $color_id_position = array_search($color_id, $color_position);
                        $size_id_position = array_search($size_id, $size_position);
                        array_push($select_position, array(
                            "size_id" => $size_id,
                            "color_id" => $color_id,
                            "size_id_position" => $size_id_position,
                            "color_id_position" => $color_id_position,
                            "custom_stock_status" => $productStockStatus
                        ));
                        $count_pr++;
                    }
                }
                if ($count_pr > 0) {
                    usort($select_position, function ($one, $two) {
                        if ($one['size_id_position'] == $two['size_id_position']) {
                            if ($one['color_id_position'] == $two['color_id_position']) return 0;

                            return ($one['color_id_position'] > $two['color_id_position']) ? 1 : -1;
                        }
                        return ($one['size_id_position'] > $two['size_id_position']) ? 1 : -1;
                    });

                    $select_config = array('sa_count' => 2, 'color_id' => $color_position_1[$select_position[0]['color_id_position']], 'size_id' => $size_position_1[$select_position[0]['size_id_position']], 'attribute_id' => $masterAttributeId['size_id'], 'custom_stock_status' => $select_position[0]['custom_stock_status'], 'count_product' => $count_pr);
                } else {
                    $select_config = array('sa_count' => 2, 'color_id' => $color_position_1[0], 'size_id' => $size_position_1[0], 'attribute_id' => $masterAttributeId['size_id'], 'custom_stock_status' => $select_position[0]['custom_stock_status'], 'count_product' => $count_pr);
                }
            } elseif ($count_superAttribute == 3) {
                $color_position = array();
                $size_position = array();
                $other_position = array();
                $color_position_1 = array();
                $size_position_1 = array();
                $other_position_1 = array();
                $masterAttributeId = array();

                foreach ($productAttributeOptions as $masterAttribute) {
                	
                    if (strpos($masterAttribute['attribute_code'], 'color') !== false || strpos($masterAttribute['attribute_code'], 'metal_color') !== false) {
                        $masterAttributeId['color_code'] = $masterAttribute['attribute_code'];
                        $masterAttributeId['color_id'] = $masterAttribute['attribute_id'];
                        foreach ($masterAttribute['values'] as $attributeValue) {
                            $color_position_1[] = $attributeValue['value_index'];
                            $color_position[] = $attributeValue['label'];
                        }
                    } elseif (strpos($masterAttribute['attribute_code'], 'size') !== false || strpos($masterAttribute['attribute_code'], 'length') !== false || strpos($masterAttribute['attribute_code'], 'frontal_post_option') !== false) {
                        $masterAttributeId['size_code'] = $masterAttribute['attribute_code'];
                        $masterAttributeId['size_id'] = $masterAttribute['attribute_id'];
                        foreach ($masterAttribute['values'] as $attributeValue) {
                            $size_position_1[] = $attributeValue['value_index'];
                            $size_position[] = $attributeValue['label'];
                        }
                    } else {
                        $masterAttributeId['other_code'] = $masterAttribute['attribute_code'];
                        $masterAttributeId['other_id'] = $masterAttribute['attribute_id'];
                        foreach ($masterAttribute['values'] as $attributeValue) {
                            $other_position_1[] = $attributeValue['value_index'];
                            $other_position[] = $attributeValue['label'];
                        }
                    }
                }
                
                $select_position = array();
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $count_pr = 0;
                foreach ($childProducts as $childProduct) {
                    $childProductId = $childProduct->getId();
                    $productQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProductId);
                    $productStockStatus = Mage::getModel('catalog/product')->load($childProductId)->getAttributeText('custom_stock_status');
                    $pQty = $productQty->getQty();
                    if ((int)$pQty > 0) {
                        $color_id 	= ($masterAttributeId['color_code'] ? Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['color_code']) : null);
                        $size_id 	= ($masterAttributeId['size_code'] ? Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['size_code']) : null);
                        $other_id 	= ($masterAttributeId['other_code'] ? Mage::getModel('catalog/product')->load($childProductId)->getAttributeText($masterAttributeId['other_code']) : null);
                        $color_id_position = array_search($color_id, $color_position);
                        $size_id_position = array_search($size_id, $size_position);
                        $other_id_position = array_search($other_id, $other_position);
                        array_push($select_position, array(
                            "size_id" => $size_id,
                            "color_id" => $color_id,
                            "other_id" => $other_id,
                            "size_id_position" => $size_id_position,
                            "color_id_position" => $color_id_position,
                            "other_id_position" => $other_id_position,
                            "custom_stock_status" => $productStockStatus
                        ));
                        $count_pr++;
                    }
                }
                $data_size = array();
                foreach ($select_position as $key => $arr) {
                    $data_size[$key] = $arr['size_id_position'];
                }

                $data_color = array();
                foreach ($select_position as $key => $arr) {
                    $data_color[$key] = $arr['color_id_position'];
                }

                $data_other = array();
                foreach ($select_position as $key => $arr) {
                    $data_other[$key] = $arr['other_id_position'];
                }
                if ($count_pr > 0) {
                    array_multisort($data_size, SORT_NUMERIC, $data_color, $data_other, $select_position);
                    $select_config = array('sa_count' => 3,
                        'color_id' => $color_position_1[$select_position[0]['color_id_position']],
                        'size_id' => $size_position_1[$select_position[0]['size_id_position']],
                        'attribute_id' => $masterAttributeId['size_id'],
                        'other_value' => $other_position_1[$select_position[0]['other_id_position']],
                        'other_id' => $masterAttributeId['other_id'],
                        'custom_stock_status' => $select_position[0]['custom_stock_status'],
                        'count_product' => $count_pr);
                } else {
                    $select_config = array('sa_count' => 3,
                        'color_id' => $color_position_1[0],
                        'size_id' => $size_position_1[0],
                        'attribute_id' => $masterAttributeId['size_id'],
                        'other_value' => $other_position_1[0],
                        'other_id' => $masterAttributeId['other_id'],
                        'custom_stock_status' => $select_position[0]['custom_stock_status'],
                        'count_product' => $count_pr);
                }
            }
        }
        

        $this->getResponse()->setHeader('Access-Control-Allow-Origin', '*');

        if(!is_null($product)) {
            $this->getResponse()->setBody(json_encode(array('price' => $this->getPriceHtml($product, true), 'jsonconfigtest' => $config, 'jsonconfigselect' => $select_config)));
            $this->getResponse()->sendResponse();
        } else {
        	$this->getResponse()->setBody("{}");
        	$this->getResponse()->sendResponse();
        }
        exit;
    }
    
}