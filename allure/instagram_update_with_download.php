<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$lower = $_GET['lower'];
$upper = $_GET['upper'];

if(empty($lower) && empty($upper)){
    //die("Please mention params");
}

try{
    $collection = Mage::getModel('allure_instacatalog/feed')->getCollection();
    $collection->addFieldToFilter('entity_id', array('gteq' => $lower));
    $collection->addFieldToFilter('entity_id', array('lteq' => $upper));
    echo "Size : ".$collection->getSize()."<br>";
    if($collection->getSize() > 0){
        foreach ($collection as $item){
    try{
        $isUpdate        = false;

        $feed = Mage::getModel("allure_instacatalog/feed")->load($item->getId());
        if($feed->getId()){

            if(!empty($feed->getMediaId())) {

                $imagesArray=getImagesByMediaId($feed->getMediaId());

            }
            else{
                $instagramUrl = "http://api.instagram.com/oembed?url={$feed->getUsername()}";
                $response        = file_get_contents($instagramUrl);
                $responseObj     = json_decode($response,true);
                $responseObj['html']  = "";
                $media_id        = $responseObj['media_id'];

                $imagesArray=getImagesByMediaId($feed->getMediaId());
                //$imageUrl        = savePicture($responseObj['thumbnail_url'],$item->getImage());
            }


            if(!empty($imagesArray)) {
                $standImageUrl = savePicture($imagesArray['stand']['url'], $imagesArray['stand']['res']);
                $lowImageUrl = savePicture($imagesArray['low']['url'], $imagesArray['low']['res']);
                $thumbImageUrl = savePicture($imagesArray['thumb']['url'], $imagesArray['thumb']['res']);
            }
            else{
                $instagramUrl = "http://api.instagram.com/oembed?url={$feed->getUsername()}";
                $response        = file_get_contents($instagramUrl);
                $responseObj     = json_decode($response,true);
                $responseObj['html']  = "";

                if (!@getimagesize($responseObj['thumbnail_url'])) {
                    $image_url=$feed->getStandardResolution();
                    }
                    else
                    {
                        $imageUrl=$responseObj['thumbnail_url'];
                    }

                $standImageUrl=$lowImageUrl=$thumbImageUrl  = savePicture($imageUrl,"standard_resolution");
            }



            if(!empty($media_id)){
                $feed->setMediaId($media_id);
                $isUpdate = true;
            }
            if(!empty($standImageUrl)){
                $feed->setImage($standImageUrl);
                $feed->setStandardResolution($standImageUrl);
                $feed->setLowResolution($lowImageUrl);
                $feed->setThumbnail($thumbImageUrl);
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



function savePicture($image_url,$res){

    try {
        $iow = new Varien_Io_File();
        $iow->setAllowCreateFolders(true);

        $pathStandRes = Mage::getBaseDir('media') . DS . 'insta-Images'.DS.$res;
        if(!file_exists($pathStandRes))
        {
            $iow->mkdir($pathStandRes);
        }

        $path=array('dir'=>$pathStandRes,'url'=>Mage::getBaseUrl('media').'insta-Images/'.$res);
    }
    catch(Exception $e)
    {
        //echo $e->getMessage();
    }



    $filename = basename($image_url);
    $filenameArray=explode("?",$filename);
    $filename=$filenameArray[0];




    $destination = $path['dir'] ."/". $filename;

    if (!is_dir($path['dir']) or !is_writable($path['dir'])) {
        echo "path is not writable";
    } elseif (is_file($destination) and !is_writable($destination)) {
        echo "image path not writable";
    }

    if(file_put_contents($destination, file_get_contents($image_url))!=false) {
       // echo "file downloaded using new url";
        return $path['url']."/".$filename;
    }else {
        echo "Error! image cant be download <br>";
            return '';
    }
}


function getImagesByMediaId($media_id)
{
     $access_token = Mage::getStoreConfig('allure_instacatalog/feed/access_token');
     $instagramUrlByMediaId = "https://api.instagram.com/v1/media/{$media_id}/?access_token={$access_token}";
     $response1 = file_get_contents($instagramUrlByMediaId);
     $responseObj1 = json_decode($response1, true);


    if(!$response1)
    {
        echo "<br> Media id response false- {$media_id} <br>";
        return '';
    }

    $thumbImgUrl = $responseObj1['data']['images']['thumbnail']['url'];
    $lowResImgUrl = $responseObj1['data']['images']['low_resolution']['url'];
    $standResImgUrl = $responseObj1['data']['images']['standard_resolution']['url'];
    $images=array(
        'thumb'=> array('res'=>'thumbnails','url'=>$thumbImgUrl),
        'low'=>array('res'=>'low_resolution','url'=>$lowResImgUrl),
        'stand'=>array('res'=>'standard_resolution','url'=>$standResImgUrl)
    );

    return $images;
}

