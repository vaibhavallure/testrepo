<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$lower = $_GET['lower'];
$upper = $_GET['upper'];

if(empty($lower) && empty($upper)){
    die("Please mention params");
}

try{
    $collection = Mage::getModel('allure_instacatalog/feed')->getCollection();
    $collection->addFieldToFilter('entity_id', array('gteq' => $lower));
    $collection->addFieldToFilter('entity_id', array('lteq' => $upper));
    echo "Size : ".$collection->getSize()."<br>";
    if($collection->getSize() > 0){
        foreach ($collection as $item){
            try{
                $feed = Mage::getModel("allure_instacatalog/feed")->load($item->getId());
                if($feed->getId()){
                    $instagramUrl = "http://api.instagram.com/oembed?url={$feed->getUsername()}";
                    $response        = file_get_contents($instagramUrl);
                    $responseObj     = json_decode($response,true);
                    $responseObj['html']  = "";
                    $media_id        = $responseObj['media_id'];
                    $imageUrl        = $responseObj['thumbnail_url'];
                    $isUpdate        = false;
                    if(!empty($media_id)){
                        $feed->setMediaId($media_id);
                        $isUpdate = true;
                    }
                    if(!empty($imageUrl)){
                        $feed->setImage($imageUrl);
                        $isUpdate = true;
                    }
                    if($isUpdate){
                        $feed->save();
                    }
                }
            }catch (Exception $ee){
                var_dump("Err: ".$ee->getMessage());
            }
            $feed = null;
        }
    }else{
        echo "No more data found.<br>";
    }
}catch (Exception $e){
    var_dump("Exception: ".$e->getMessage());
}
die("Finish...");