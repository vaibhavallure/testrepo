<?php
class Teamwork_Common_Model_Chq_Api_Dependency
{
    public $dependencyMapping = array(
        Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENTORY_EXPORT => array(
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CHANNEL_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_FIELD_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ATTRIBUTESET_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENBRAND_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENDEPTSET_EXPORT, // +- add GUIDs
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CATEGORY_EXPORT,
        ),
        Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENDEPTSET_EXPORT => array(
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENCLASS_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENDEPARTMENT_EXPORT,
        ),
        Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CHANNEL_EXPORT => array(
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_LOCATION_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_SERVICEFEE_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_DISCOUNTREASON_EXPORT,
        ),
    );
    
    public function getDependency($type)
    {
        $dependency = array();
        if( !empty($this->dependencyMapping[$type]) )
        {
            $dependency = $this->dependencyMapping[$type];
        }
        
        return $dependency;
    }
}