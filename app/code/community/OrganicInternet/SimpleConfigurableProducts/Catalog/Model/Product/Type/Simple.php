<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple
    extends Mage_Catalog_Model_Product_Type_Simple
{
    #Later this should be refactored to live elsewhere probably,
    #but it's ok here for the time being
    private function getCpid()
    {
        $cpid = $this->getProduct()->getCustomOption('cpid');
        if ($cpid) {
            return $cpid;
        }

        $br = $this->getProduct()->getCustomOption('info_buyRequest');
        if ($br) {
            $brData = unserialize($br->getValue());
            if(!empty($brData['cpid'])) {
                return $brData['cpid'];
            }
        }

        return false;
    }

    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }
        return array($product);
    }

    public function hasConfigurableProductParentId()
    {
        $cpid = $this->getCpid();
        return !empty($cpid);
    }

    public function getConfigurableProductParentId()
    {
        return $this->getCpid();
    }
    public function getConfigurableAttributesAsArray($product = null)
    {
    	$res = array();
    	foreach ($this->getConfigurableAttributes($product) as $attribute) {
    		$res[] = array(
    				'id'             => $attribute->getId(),
    				'label'          => $attribute->getLabel(),
    				'use_default'    => $attribute->getUseDefault(),
    				'position'       => $attribute->getPosition(),
    				'values'         => $attribute->getPrices() ? $attribute->getPrices() : array(),
    				'attribute_id'   => (!is_null($attribute->getProductAttribute()))?$attribute->getProductAttribute()->getId():"",
    				'attribute_code' => (!is_null($attribute->getProductAttribute()))?$attribute->getProductAttribute()->getAttributeCode():"",
    				'frontend_label' => (!is_null($attribute->getProductAttribute()))?$attribute->getProductAttribute()->getFrontend()->getLabel():"",
    				'store_label'    => (!is_null($attribute->getProductAttribute()))?$attribute->getProductAttribute()->getStoreLabel():"",
    		);
    	}
    	return $res;
    }
}
