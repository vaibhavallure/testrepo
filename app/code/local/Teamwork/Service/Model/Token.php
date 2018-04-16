<?php
class Teamwork_Service_Model_Token extends Mage_Core_Helper_Abstract
{
    public $prefix = "Namespace: ";
    public function getCommentText()
    {
        $namespaceInformation = Mage::getSingleton('teamwork_service/dam')->getResource()->getNamespace();
        if( !empty($namespaceInformation['namespace']) )
        {
            return "{$this->prefix}<strong style='color:green'>{$namespaceInformation['namespace']}</strong>";
        }
        else
        {
            return $this->defaultNoComment();
        }
    }
    
    public function defaultNoComment()
    {
        return "{$this->prefix}<strong style='color:red'>No valid token defined</strong>";
    }
}