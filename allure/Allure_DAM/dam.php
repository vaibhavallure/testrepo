<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

class Dam
{
    public function getProducts($start = 0, $limit = 1)
    {
        $pluattrid=$this->getPluAttributeId();
        $query="SELECT plu.value as PLU,gal.value as image FROM `catalog_product_entity_media_gallery` gal JOIN `catalog_product_entity_media_gallery_value` val ON val.value_id=gal.value_id JOIN `catalog_product_entity_text` plu ON gal.entity_id=plu.entity_id WHERE plu.attribute_id='{$pluattrid}' AND val.position=1 LIMIT {$start},{$limit}";
        $products=$this->readQuery($query);

        $this->log("start:".$start." -limit:".$limit);
        $this->log("product count :".count($products));

        $basePath=Mage::getBaseDir('media')."/catalog/product/";

        $count=$start;
        foreach ($products as $product)
        {
            $this->log($count.")product plu:".$product['PLU']);
            $filePath= $basePath."".$product['image'];
            if(file_exists($filePath))
            {
                $img = base64_encode(file_get_contents($filePath));

                $imageData=[
                    'plu'=>$product['PLU'],
                    'name'=> basename($filePath),
                    'data'=>$img
                ];

                if($this->getDamClient()->syncImage($imageData))
                {
                     $this->log("image sent to DAM");
                }else{
                    $this->log("image not sent to DAM (please check dam api log for error)");
                }
            }else{
                $this->log("File Not Found:".$product['image']);
            }

            $count++;
        }
    }

    private function getPluAttributeId()
    {
        $query = "SELECT `attribute_id`  FROM `eav_attribute` WHERE `attribute_code` LIKE '%teamwork_plu%'";
        $rows= $this->readQuery($query);
        return $rows[0]['attribute_id'];
    }

    private function readQuery($query)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $connection->fetchAll($query);
    }

    private function getDamClient(){
        return Mage::helper("teamworkdam/teamworkDAMClient");
    }

    private function log($messge)
    {
        Mage::log($messge,7,"allure_teamwork_dam_api_script.log",true);
    }
}

$dam=new Dam();
$dam->getProducts();


