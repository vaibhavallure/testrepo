<?php
class Teamwork_Common_Helper_Staging_Abstract extends Mage_Core_Helper_Abstract
{
    public function getSaltedRequestId($requestId, $salt)
    {
        return Mage::helper('teamwork_common/guid')->getGuidFromString( strtolower($requestId . $salt) );
    }
    
    public function getTableDescriptionByResource($resource)
    {
        return $resource->getReadConnection()->describeTable( $resource->getMainTable() );
    }
    
    public function isGuidColumn($column)
    {
        return ($column['DATA_TYPE'] == 'char' && $column['LENGTH'] == 36) ? true : false;
    }
}