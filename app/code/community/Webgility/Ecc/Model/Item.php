<?php
/*© Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/

class Webgility_Ecc_Model_Item
{
    private $Item = array();
    
    public function setItemID($ItemID)
    {
        $this->Item['ItemID'] = $ItemID ? $ItemID :'';
    }
    public function setItemCode($ItemCode)
    {
        $this->Item['ItemCode'] = $ItemCode ? $ItemCode :'';
    }
    public function setItemDescription($ItemDescription)
    {
        $this->Item['ItemDescription'] = $ItemDescription ? $ItemDescription : '';
    }
    public function setItemShortDescr($ItemShortDescr)
    {
        $this->Item['ItemShortDescr'] = $ItemShortDescr ? $ItemShortDescr : '';
    }
    public function setCategories($Categories)
    {
        $this->Item['Categories'][] = $Categories ? $Categories : '';
    }
    public function setManufacturer($Manufacturer)
    {
        $this->Item['manufacturer'] = $Manufacturer ? $Manufacturer : '';
    }
    public function setQuantity($Quantity)
    {
        $this->Item['Quantity'] = $Quantity ? $Quantity : 0;
    }
    public function setUnitPrice($UnitPrice)
    {
        $this->Item['UnitPrice'] = $UnitPrice ? $UnitPrice : 0;
    }
    public function setCostPrice($CostPrice)
    {
        $this->Item['CostPrice'] = $CostPrice ? $CostPrice : 0;
    }
    public function setListPrice($ListPrice)
    {
        $this->Item['ListPrice'] = $ListPrice ? $ListPrice : 0;
    }
    public function setWeight($Weight)
    {
       $this->Item['Weight'] = $Weight ? $Weight : 0;
    }
    public function setLowQtyLimit($LowQtyLimit)
    {
        $this->Item['LowQtyLimit'] = $LowQtyLimit ? $LowQtyLimit : 0;
    }
    public function setFreeShipping($FreeShipping)
    {
        $this->Item['FreeShipping'] = $FreeShipping ? $FreeShipping : 0;
    }
    public function setDiscounted($Discounted)
    {
        $this->Item['Discounted'] = $Discounted ? $Discounted : 0;
    }
    public function setShippingFreight($ShippingFreight)
    {
        $this->Item['ShippingFreight'] = $ShippingFreight ? $ShippingFreight : 0;
    }
    public function setWeight_Symbol($Weight_Symbol)
    {
        $this->Item['Weight_Symbol'] = $Weight_Symbol ? $Weight_Symbol : 0;
    }
    public function setWeight_Symbol_Grams($Weight_Symbol_Grams)
    {
        $this->Item['Weight_Symbol_Grams'] = $Weight_Symbol_Grams ? $Weight_Symbol_Grams : 0;
    }
    public function setTaxExempt($setTaxExempt)
    {
        $this->Item['setTaxExempt'] = $setTaxExempt ? $setTaxExempt : 0;
    }
    public function setUpdatedAt($UpdatedAt)
    {
        $this->Item['UpdatedAt'] = $UpdatedAt ? $UpdatedAt : 0;
    }
    public function setCreatedAt($CreatedAt)
    {
        $this->Item['CreatedAt'] = $CreatedAt ? $CreatedAt : 0;
    }
    public function setImageUrl($ImageUrl)
    {
        $this->Item['ImageUrl'] = $ImageUrl ? $ImageUrl : '';
    }
    public function setItemVariants($ItemVariants)
    {
        $this->Item['ItemVariants'][] = $ItemVariants ? $ItemVariants : '';
    }
    public function setItemOptions($ItemOptions)
    {
        $this->Item['ItemOptions'][] = $ItemOptions ? $ItemOptions : '';
    }

	#Extra node fro Orders
    public function setShippedQuantity($ShippedQuantity)
    {
        $this->Item['ShippedQuantity'] = $ShippedQuantity ? $ShippedQuantity : 0;
    }
    public function setOneTimeCharge($OneTimeCharge)
    {
        $this->Item['OneTimeCharge'] = $OneTimeCharge ? $OneTimeCharge : 0;
    }
    public function setItemTaxAmount($ItemTaxAmount)
    {
        $this->Item['ItemTaxAmount'] = $ItemTaxAmount ? $ItemTaxAmount : 0;
    }

#Nodes used for add product
    public function setStatus($Status)
    {
        $this->Item['Status'] = $Status ? $Status : '';
    }
    public function setProductID($ProductID)
    {
        $this->Item['ProductID'] = $ProductID ? $ProductID : '';
    }
    public function setSku($Sku)
    {
        $this->Item['Sku'] = $Sku ? $Sku : '';
    }
    public function setProductName($ProductName)
    {
        $this->Item['ProductName'] = $ProductName ? $ProductName : '';
    }
#node for sync product
    public function setItemUpdateStatus($ItemUpdateStatus)
    {
       $this->Item['ItemUpdateStatus'] = $ItemUpdateStatus ? $ItemUpdateStatus : '';
    }
    public function setPrice($Price)
    {
       $this->Item['Price'] = $Price ? $Price : 0;
    }
    public function setItemType($ItemType)
    {
        $this->Item['ItemType']=$ItemType?$ItemType:"";
    }
    public function getItem()
    {
        return $this->Item;
    }
}