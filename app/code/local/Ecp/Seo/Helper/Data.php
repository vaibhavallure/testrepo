<?php
/**
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSeoCategory($categoryId){
        $categorySeo = Mage::getModel('catalog/category')->load($categoryId)->getSeo();
        if(!empty($categorySeo)){
            return $categorySeo;
        }else{
            return false;
        }
    }

    public function prepareContent($content){
        $string = array();
        $start = 0;
        $limit = 200;
        $pos=1;
        if(strlen($content)>200){
            while($pos==1){
                $cadena = substr($content,$start,$limit);
                $x=1;

                if(substr($content,strlen($cadena),1)!=' '){
                    if((int)(strlen($cadena) - $x)> 0) while($cadena[strlen($cadena) - $x] != ' '){
                        $x+=1;
                    }
                }

                $thisCadena = substr($cadena,0,strlen($cadena) - $x);

                if(strlen($thisCadena)==0){
                    $pos=0;
                }else{
                    $string[] = $thisCadena;
                    $start += strlen($thisCadena);
                }
            }
            return $string;
        }else{
            return array($content);
        }

    }
}
