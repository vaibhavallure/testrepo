<?php 
class Ecp_Familycolors_Helper_Image extends Mage_Core_Helper_Abstract
{
    function getResizeWidth ($maxWidth, $img)
    {
        $originalWidth = $img->getOriginalWidth ();
        $originalHeight = $img->getOriginalHeight ();
        $percent = $maxWidth / $originalWidth;
        
        return $originalHeight*$percent;
    }
}