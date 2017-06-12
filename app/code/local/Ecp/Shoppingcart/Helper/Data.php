<?php

//GCCart
class Ecp_Shoppingcart_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCarrierName($carrierCode) {
        if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function dispatchPriceAlert() {		
        $cookie = Mage::getSingleton('core/cookie');
        $_json = json_decode($cookie->get('current_cart'),true);
        $_jsonkey = array();
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        if (count($_json)) {
            foreach($_json as $key=>$value) {
                $_jsonkey[] = $key;
            }
        }
        $_update = false;
        foreach($items as $item) {
            $product = $item->getProduct();
            $_jsonNew[$product->getId()] = $product->getPrice();
            if(in_array($item->getProductId(),$_jsonkey)) {
                if($_json[$item->getProductId()] != $product->getPrice()) {
                 $_update = true;          
                }
            }
        }
        $cookie->set('current_cart',json_encode($_jsonNew),time()+60*60*24*15);
        return $_update;
    }
	public function attributesAction()
    {        
        $resource = Mage::getSingleton('core/resource');
        $db = $resource->getConnection('core_read');

        $select = $db->select()
            ->from($resource->getTableName('eav/entity_type'), 'entity_type_id')
            ->where('entity_type_code=?', 'catalog_product')
            ->limit(1);

        $_entityTypeId = $db->fetchOne($select);

        $select = $db->select()
            ->from($resource->getTableName('eav/attribute'), array(
                    'title' => 'frontend_label',          // for admin part
                    'id'    => 'attribute_id',             // for applying filter to collection
                    'code'  => 'attribute_code',    // as a tip for constructing {attribute_name}
                    'type'  => 'backend_type',    // for table name
                ))
            ->where('entity_type_id=?', $_entityTypeId)
            ->where('frontend_label<>""')
            ->orWhere('attribute_code=?', "manufacturer")  //add new attribute in backend
            ->where('find_in_set(backend_type, "text,varchar,static")')
            ->order('frontend_label');

        foreach($db->fetchAll($select) as $v) {
            $_productAttributes[$v['id']] = array(
                    'title' => $v['title'],
                    'code'  => $v['code'],
            );
			$_attrIds[] = $v['id'];
		}
		$newArr = '(' . implode(',', $_attrIds) . ')';
		$selectAttr = $db->select()
            ->from($resource->getTableName('catalog_eav_attribute'), array(
                    'is_configurable' => 'is_configurable',          // for admin part
                    'id'    => 'attribute_id',   // for table name
                ))
			->where('attribute_id IN '. $newArr)
			->where('is_global=1')
			->where('is_configurable=1');
		$excludeAttr = Array('available_on_try_on_studio','size_chart');	
		foreach($db->fetchAll($selectAttr) as $v) {            
			$_attrIds2[] = $v['id'];
			if(!in_array($_productAttributes[$v['id']]['code'],$excludeAttr) )
				$_attributes[$_productAttributes[$v['id']]['code']] = $_productAttributes[$v['id']]['title'];
		}	
		return $jsonArr = json_encode($_attributes);
    }

}