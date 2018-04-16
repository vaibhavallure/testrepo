<?php

class Teamwork_Service_Helper_Parse extends Mage_Core_Helper_Abstract
{
    public function getElementVal($xmlNode, $childNode = false, $attrNode = false)
    {
        $result = null;
        if (!empty($xmlNode))
        {
            $attr = $xmlNode->attributes('xsi', true);

            if(!(isset($attr['nil']) && $attr['nil'] == 'true'))
            {
                if(!empty($childNode))
                {
                    $result = (string) $xmlNode->$childNode;
                }
                elseif(!empty($attrNode))
                {
                    $result = (string) $xmlNode[$attrNode];
                }
                else
                {
                    $result = (string) $xmlNode;
                }
            }
        }
        return $result;
    }
}