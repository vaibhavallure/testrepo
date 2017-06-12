<?php
/**
 * Description of Citysearch
 *
 * @category    Ecp
 * @package     Ecp_Citysearch
 */
class Ecp_Citysearch_Block_Citysearch extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    protected $jsonApiResponse;
    
    protected function _init($listingId){    
        
        $options = unserialize(Mage::getStoreConfig('ecp_citysearch/citysearch/api_citysearch_new_config'));        
        if(is_array($options)){
            $idx = $this->multiarray_search($options, 'listing_id', $listingId);            
            if($idx !=-1 ){                
                
                $detailUrl = Mage::getStoreConfig('ecp_citysearch/citysearch/detail_api_url');
                $listingId = $options[$idx]['listing_id'];
                $idType = $options[$idx]['type_id'];
                $publisher = $options[$idx]['publisher'];
                $clientIp = $options[$idx]['client_ip'];        
                
                $detailPlaceEndpoint = $detailUrl."format=json&id=$listingId&id_type=$idType&client_ip=$clientIp&publisher=$publisher";

                $curl_handle = curl_init($detailPlaceEndpoint);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($curl_handle);
                curl_close($curl_handle);                   

                $this->jsonApiResponse = json_decode($response);
            }
        }
    }
    
    protected function _construct(){
        parent::_construct();
    }        
    
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }
    
    protected function _toHtml(){          
        $this->_init($this->getData('citysearch_account'));
        $this->setTemplate('citysearch/citysearch.phtml');
        return parent::_toHtml();
    }
    
    public function getJsonApiResponse(){
        return $this->jsonApiResponse;
    }
    
    public function getErrorsResponse(){
        try{           
            return $this->jsonApiResponse->errors;
        }catch(Exception $e){
            return false;
        }
    }
        
    public function getMapUrl(){
         $listId  = $this->getData('citysearch_account');
         $options = unserialize(Mage::getStoreConfig('ecp_citysearch/citysearch/api_citysearch_new_config_map')); 
         foreach($options as $info){
             if($info['id'] == $listId){                 
                 return $info['map'];
             }
         }
         return '#';
        
    }
    public function getTotalUserReviews(){
        return $this->jsonApiResponse->locations[0]->review_info->total_user_reviews;
    }
    
    public function getOverallReviewRating(){
        return $this->jsonApiResponse->locations[0]->review_info->overall_review_rating;
    }
    
    public function getName(){
        return $this->jsonApiResponse->locations[0]->name;
    }
       
    public function getCategories(){
        $categories = array();        
        foreach($this->jsonApiResponse->locations[0]->categories as $key=>$value){
            !in_array($value->parent, $categories) ? $categories[] = $value->parent : "";
        }       
        return implode(", ",$categories);
    }
    
    public function getAddress(){
        $addresInfo = $this->jsonApiResponse->locations[0]->address;
        return $addresInfo->street.", ".$addresInfo->city.", ".$addresInfo->state;
    }
    
    public function getNeighborhood(){
        return implode(", ",$this->jsonApiResponse->locations[0]->neighborhoods);
    }
    
    function multiarray_search($arrayVet, $campo, $valor){
        while(isset($arrayVet[key($arrayVet)])){

            $searchin = explode("|",$arrayVet[key($arrayVet)][$campo]);

            foreach($searchin as $s){
                if($s === $valor){
                    return key($arrayVet);
                }
            }
            next($arrayVet);
        }
        return -1;
    }
    
}