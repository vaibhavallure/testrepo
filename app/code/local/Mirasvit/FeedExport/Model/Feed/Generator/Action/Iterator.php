<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.5
 * @build     711
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator extends Mirasvit_FeedExport_Model_Feed_Generator_Action
{
    public function process()
    {

        switch ($this->getType()) {
            case 'rule':
                $iteratorModel = Mage::getModel('feedexport/feed_generator_action_iterator_rule');
                break;

            case 'product':
            case 'category':
            case 'review':
                $iteratorModel = Mage::getModel('feedexport/feed_generator_action_iterator_entity');
                break;

            default:
                Mage::throwException(sprintf('Undefined iterator type %s', $this->getType()));
                break;
        }

        $iteratorModel
            ->setData($this->getData())
            ->setFeed($this->getFeed());

        if ($iteratorModel->init() === false) {
            $this->finish();
            return;
        }

        $collection = $iteratorModel->getCollection();
        $size       = $collection->getConnection()->fetchOne($collection->getSelectCountSql());
        $idx        = intval($this->getValue('idx'));
        $add        = intval($this->getValue('add'));

        if ($idx == 0) {
            $this->start();
            $iteratorModel->start();
        }

        $limit = intval($size / 100);
        if ($limit < 100) {
            $limit = 100;
        }

        $resource   = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $collection->getSelect()->limit($limit, $idx);
        if ($this->getFeed()->getGenerator()->getMode() == 'test') {
            if ($ids = Mage::app()->getRequest()->getParam('ids')) {
                $ids = explode(',', $ids);
                if ($this->getType() == 'review') {
                    $collection->addFieldToFilter('main_table.review_id', $ids);
                } else {
                    $collection->addFieldToFilter('entity_id', $ids);
                }
            } else {
                $collection->getSelect()
                    ->order(new Zend_Db_Expr('RAND()'))
                    ->limit(100);
            }
            if ($size > $collection->count()) {
                $size = $collection->count() - 1;
            }
        }

        $stmt       = $connection->query($collection->getSelect());
        $result     = array();
        while ($row = $stmt->fetch()) {
            $callbackResult = $iteratorModel->callback($row);
            if ($callbackResult !== null) {
                $result[] = $callbackResult;
                $add++;
            }
            $idx++;

            $this->setValue('idx', $idx)
                ->setValue('size', $size)
                ->setValue('add', $add);

            if (Mage::helper('feedexport')->getState()->isTimeout()) {
                break;
            }
        }

        /*New code for configurable product variations*/
        $id = $this->getFeed()->getId();
        $feed_custom = Mage::getModel('feedexport/feed')->load($id);
        $feedName =strtolower($feed_custom->getName());

        if(strpos($feedName, 'custom') !== false) {

            switch ($this->getType()) {
                case 'product':
                    try {
                        $this->log('Feed' . $id);
                        $this->log('Feed Name' . $feedName);
                        $this->log('Product Result');
                        $this->log($result);
                        $this->log('creating custom array');
                        $result = $this->getCustomResult($result,$feed_custom);
                        $this->log($result);
                        $iteratorModel->save($result);
                    } catch (Exception $ex) {
                        $this->log('Exceptions' . $ex->getMessage());
                    }
                    break;
                default:
                    $iteratorModel->save($result);
                    break;

            }
        }
        else{
            $iteratorModel->save($result);
        }


        if ($idx >= $size) {
            $iteratorModel->finish();
            $this->finish();
            $this->setIteratorType($this->getKey());
        }
    }
    public  function log($msg)
    {
        Mage::log($msg, Zend_Log::DEBUG, 'search.log', true);
    }

    public function getCustomResult($result,$feed_custom)
    {
        $this->log('In Custom Result');
        $mapping = $feed_custom->getMapping();
        $headers = $mapping['header'];
        $this->log($headers);
        $colorIndex = array_search('color', $headers);
        $titleIndex = array_search('title', $headers);
        $imageIndex = array_search('image_link', $headers);
        $priceIndex = array_search('price',$headers);
        $urlIndex = array_search('link',$headers);
       try {
           $newResultArray = array();
           foreach ($result as $product) {

               $newProducts = array();
               $dataArr = preg_split("/[\t]/", $product);
               $index = array_search('give_color', $dataArr);

               if ($index) {
                   $dataArr[$index] = 'new_color';
               }

               $product = Mage::getModel('catalog/product')->load($dataArr[0]);
               if($product->getId() == '43282'){
                   Mage::log(json_encode($product->getData(),true),Zend_Log::DEBUG,'adi.log',true);
               }
               if($dataArr[$priceIndex]!=0) {
                   $dataArr[$priceIndex] = $dataArr[$priceIndex] . ' USD';
               }
               else{
                   $dataArr[$priceIndex] = round($product->getData('price'),2) . ' USD';
               }
               /*CHECK FOR PRODUCT URL IF CONTAIS CATALOG/PRODUCT/VIEW the get url_path*/
               $productUrlNew='';
               if(isset($dataArr[$urlIndex]))
               {
                   $productUrlNew = $dataArr[$urlIndex];
                   if(strpos($productUrlNew, '/catalog/product/view/') !== false){
                       $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                       $productUrlNew=$baseUrl.$product->getUrlPath().'?fee='.$feed_custom->getId()."&fep".$product->getId();
                       $dataArr[$urlIndex] = $productUrlNew;
                   }

               }

               if($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE){
               $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
               foreach ($childProducts as $child) {
                   $color = $imgSource = '';
                   $metal = $child->getAttributeText('metal');
                   if (!empty($metal)) {
                       $color = $metal;
                       if($child->getThumbnail()){
                       $imgSource = Mage::getModel('catalog/product_media_config')->getMediaUrl($child->getThumbnail());
                       }
                       else
                       {
                           $imgSource = $dataArr[$imageIndex];
                       }
                       $newItem = array('color' => $color, 'image' => $imgSource);
                       array_push($newProducts, $newItem);

                   } else {
                       $new_product = $dataArr;
                       $new_product[$colorIndex] = 'NO VARIANTS';
                       $new_product_string = implode("\t", $new_product);
                       array_push($newResultArray, $new_product_string);
                   }
               }
               $newProducts = $this->unique_multidim_array($newProducts, 'color');
               foreach ($newProducts as $new_product) {
                   $color = $new_product['color'];
                   $imageUrl = $new_product['image'];
                   $new_product = $dataArr;
                   $new_product[0] = $new_product[0] . $this->getColorIntials($color);
                   if($color!='NO VARIANTS') {
                       $new_product[$titleIndex] = $new_product[$titleIndex] . ' ' . $color;
                       $new_product[$urlIndex] = $dataArr[$urlIndex].'&metal='.str_replace(' ','%20',$color);
                   }
                   $new_product[$colorIndex] = $color;
                   $new_product[$imageIndex] = $imageUrl;
                   $new_product_string = implode("\t", $new_product);
                   array_push($newResultArray, $new_product_string);
               }
               $newProducts = "";
               }
               else{
                   $color=$imgSource='';
                   $metal = $product->getAttributeText('metal');
                   if(!empty($metal))
                   {
                       $color = $metal;
                   }
                   else{
                       $color="NO VARIANTS";
                   }

                   if($product->getThumbnail()){
                       $imgSource = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());
                   }
                   else
                   {
                       $imgSource = $dataArr[$imageIndex];
                   }
                   $new_product = $dataArr;
                   $new_product[0] = $new_product[0] . $this->getColorIntials($color);
                   if($color!='NO VARIANTS') {
                       $new_product[$titleIndex] = $new_product[$titleIndex] . ' ' . $color;
                       $new_product[$urlIndex] = $new_product[$urlIndex].'&metal='.str_replace(' ','%20',$color);
                   }
                   $new_product[$colorIndex] = $color;
                   $new_product[$imageIndex] = $imgSource;
                   $new_product_string = implode("\t", $new_product);
                   array_push($newResultArray, $new_product_string);
                   $new_product="";
               }

           }
           $this->log('Custome Result'.$newResultArray);
           return $newResultArray;
       }
       catch (Exception $ex)
       {
           $this->log('Exceptions'.$ex->getMessage());
       }
    }


    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function getColorIntials($color)
    {
        $words = explode(" ", $color);
        $acronym = "";
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        if($acronym!=""){
            return '-'.$acronym;
        }
        return $acronym;
    }
}