<?php
class Teamwork_Common_Model_Chq_Api_Type
{
    const CHQ_API_TYPE_INVENTORY_EXPORT = 'inventory-export';
    const CHQ_API_TYPE_ATTRIBUTESET_EXPORT = 'attributeset-export';
    const CHQ_API_TYPE_ADJUSTMENT_EXPORT = 'adjustment-export';
    const CHQ_API_TYPE_ASN_EXPORT = 'asn-export';
    const CHQ_API_TYPE_COUNTRY_EXPORT = 'country-export';
    const CHQ_API_TYPE_CUSTOMER_EXPORT = 'customer-export';
    const CHQ_API_TYPE_EMPLOYEE_EXPORT = 'employee-export';
    const CHQ_API_TYPE_INVENBRAND_EXPORT = 'invenbrand-export';
    const CHQ_API_TYPE_INVENCLASS_EXPORT = 'invenclass-export';
    const CHQ_API_TYPE_INVENDEPARTMENT_EXPORT = 'invendepartment-export';
    const CHQ_API_TYPE_INVENDEPTSET_EXPORT = 'invendeptset-export';
    const CHQ_API_TYPE_INVENSEASON_EXPORT = 'invenseason-export';
    const CHQ_API_TYPE_LOCATION_EXPORT = 'location-export';
    const CHQ_API_TYPE_LOCATION_QUANTITY_EXPORT = 'location-quantity-export';
    const CHQ_API_TYPE_INVEN_PRICES_EXPORT = 'inven-prices-export';
    const CHQ_API_TYPE_EXTERNAL_LOCATION_QUANTITY_EXPORT = 'external-location-quantity-export';
    const CHQ_API_TYPE_POSTALCODE_EXPORT = 'postalcode-export';
    const CHQ_API_TYPE_PURCHASE_ORDER_EXPORT = 'purchase-order-export';
    const CHQ_API_TYPE_PURCHASE_RECEIPT_EXPORT = 'purchase-receipt-export';
    const CHQ_API_TYPE_SALES_ORDER_EXPORT = 'sales-order-export';
    const CHQ_API_TYPE_SALES_RECEIPT_EXPORT = 'sales-receipt-export';
    const CHQ_API_TYPE_SETTING_EXPORT = 'setting-export';
    const CHQ_API_TYPE_SHIP_SALES_ORDER_EXPORT = 'ship-sales-order-export';
    const CHQ_API_TYPE_STATE_EXPORT = 'state-export';
    const CHQ_API_TYPE_TAXCATEGORY_EXPORT = 'taxcategory-export';
    const CHQ_API_TYPE_TAXDETAIL_EXPORT = 'taxdetail-export';
    const CHQ_API_TYPE_TAXJURISDICTION_EXPORT = 'taxjurisdiction-export';
    const CHQ_API_TYPE_TAXZONE_EXPORT = 'taxzone-export';
    const CHQ_API_TYPE_TAXZONEJURISDICTION_EXPORT = 'taxzonejurisdiction-export';
    const CHQ_API_TYPE_TAXZONELINE_EXPORT = 'taxzoneline-export';
    const CHQ_API_TYPE_TIMECARD_EXPORT = 'timecard-export';
    const CHQ_API_TYPE_TRANSFER_ORDER_EXPORT = 'transfer-order-export';
    const CHQ_API_TYPE_TRANSFER_MEMO_IN_EXPORT = 'transfer-memo-in-export';
    const CHQ_API_TYPE_TRANSFER_MEMO_OUT_EXPORT = 'transfer-memo-out-export';
    const CHQ_API_TYPE_ECOMMERCE_CHANNEL_EXPORT = 'ecommerce-channel-export';
    const CHQ_API_TYPE_ECOMMERCE_FIELD_EXPORT = 'ecommerce-field-export';
    const CHQ_API_TYPE_ECOMMERCE_CATEGORY_EXPORT = 'ecommerce-category-export';
    const CHQ_API_TYPE_SERVICEFEE_EXPORT = 'servicefee-export';
    const CHQ_API_TYPE_DISCOUNTREASON_EXPORT = 'discountreason-export';
    
    const CHQ_API_TYPE_ABSTRACT_CLASS = 'abstract';
    protected static $_defaultVersion2 = false;
    protected static $_defaultChainedType = false;
    
    protected static $_typeParameters = array(
        self::CHQ_API_TYPE_INVENTORY_EXPORT => array(
            'class'         => 'product',
            'version2'      => true,
            'chained_type'  => true,
            'per_chunk'     => Teamwork_Common_Helper_Adminsettings::CHQ_API_STYLE_PER_BUTCH,
        ),
        self::CHQ_API_TYPE_INVEN_PRICES_EXPORT => array(
            'class'         => 'price',
            'version2'      => true,
            'chained_type'  => true,
            'per_chunk'     => Teamwork_Common_Helper_Adminsettings::CHQ_API_PRICE_PER_BUTCH,
        ),
        self::CHQ_API_TYPE_ATTRIBUTESET_EXPORT => array(
            'class'         => 'attribute',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_ECOMMERCE_CHANNEL_EXPORT => array(
            'class'         => 'channel',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_ECOMMERCE_CATEGORY_EXPORT => array(
            'class'         => 'category',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_ECOMMERCE_FIELD_EXPORT => array(
            'class'         => 'mappingfield',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_SERVICEFEE_EXPORT => array(
            'class'         => 'fee',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_DISCOUNTREASON_EXPORT => array(
            'class'         => 'discount',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_LOCATION_EXPORT => array(
            'class'         => 'location',
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_INVENDEPTSET_EXPORT => array(
            'class'         => 'classification',
            'version2'      => false,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_INVENCLASS_EXPORT => array(
            'class'         => 'class',
            'version2'      => false,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_INVENDEPARTMENT_EXPORT => array(
            'class'         => 'department',
            'version2'      => false,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_INVENBRAND_EXPORT => array(
            'class'         => 'brand',
            'version2'      => false,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_TAXCATEGORY_EXPORT => array(
            'class'         => 'tax',
            'version2'      => false,
            'chained_type'  => false,
        ),
        
        self::CHQ_API_TYPE_LOCATION_QUANTITY_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_SETTING_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_TRANSFER_ORDER_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_SALES_RECEIPT_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_CUSTOMER_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_SALES_ORDER_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_PURCHASE_RECEIPT_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_TRANSFER_MEMO_IN_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_TIMECARD_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_TRANSFER_MEMO_OUT_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        ),
        self::CHQ_API_TYPE_PURCHASE_ORDER_EXPORT => array(
            'class'         => self::CHQ_API_TYPE_ABSTRACT_CLASS,
            'version2'      => true,
            'chained_type'  => false,
        )
    );
    
    public static function getClassByType($apiType)
    {
        return isset(self::$_typeParameters[$apiType]) ? self::$_typeParameters[$apiType]['class'] : self::CHQ_API_TYPE_ABSTRACT_CLASS;
    }
    
    public static function isImplementedSecondVersion($apiType)
    {
        return isset(self::$_typeParameters[$apiType]) ? self::$_typeParameters[$apiType]['version2'] : self::$_defaultVersion2;
    }
    
    public static function isChainedType($apiType)
    {
        return isset(self::$_typeParameters[$apiType]) ? self::$_typeParameters[$apiType]['chained_type'] : self::$_defaultChainedType;
    }
    
    public static function getSettingForChunk($apiType)
    {
        return self::$_typeParameters[$apiType]['per_chunk'];
    }
}