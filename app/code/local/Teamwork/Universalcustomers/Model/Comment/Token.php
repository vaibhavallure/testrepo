<?php
class Teamwork_Universalcustomers_Model_Comment_Token
{
    public $prefix = "Namespace: ";
    public function getCommentText()
    {
        $svs = Mage::getSingleton('teamwork_universalcustomers/svs');
        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) 
        {
            $svs->setWebsiteId( Mage::getModel('core/website')->load($code)->getId() );
        }
        
        $namespaceInformation = $svs->getNamespace();
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