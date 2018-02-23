<?php
class Teamwork_Common_Model_Chq_Api_Dependency
{
    public $preDependencyMapping = array(
        Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENTORY_EXPORT => array(
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CHANNEL_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_FIELD_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ATTRIBUTESET_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENBRAND_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENDEPTSET_EXPORT, // +- add GUIDs
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_ECOMMERCE_CATEGORY_EXPORT,
            Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVEN_PRICES_EXPORT,
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
    
    public $postDependencyMapping = array(
        Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVENTORY_EXPORT => array(
            'mapping' => array(
                Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVEN_PRICES_EXPORT,
            ),
            'callback' => 'createProductEcm',
        ),
    );
    
    public function getPreDependency($type)
    {
        $dependency = array();
        if( !empty($this->preDependencyMapping[$type]) )
        {
            $dependency = $this->preDependencyMapping[$type];
        }
        
        return $dependency;
    }
    
    public function getPostDependency($type)
    {
        $dependency = array();
        if( !empty($this->postDependencyMapping[$type]['mapping']) )
        {
            $dependency = $this->postDependencyMapping[$type]['mapping'];
        }
        
        return $dependency;
    }
    
    public function getPostDependencyCallback($type)
    {
        if( isset($this->postDependencyMapping[$type]['callback']) )
        {
            return $this->postDependencyMapping[$type]['callback'];
        }
    }
}