<?php
class Teamwork_Service_Model_Export_Images extends Teamwork_Transfer_Model_Abstract
{
    protected $_db, $_channel_id, $_style_id;
    protected $_response, $_styles;
    protected $_attributeset, $_attributesetvalues;
    protected $_mapping;
    protected $_importedImages = array();
    protected $_productImages = array();
    protected $_error = array();

    protected $_hardcodedMapping = array(
        /*'114a4049-9ce2-4f1b-8439-6b2a00e49d66' => array(
            'image'         => array('8'),
            'small_image'   => array('8'),
            'thumbnail'     => array('8')
        )*/
    );
    const GALLERY_ATTRIBUTE_CODE = 'media_gallery';

    protected function _construct()
    {
        $this->_db = Mage::getModel('teamwork_service/adapter_db');
        $this->_generateMapping();
    }

    protected function _generateMapping()
    {
        $baseMapping = Mage::getModel('teamwork_service/mapping')->getMapping(true);
        $mapping = array();
        foreach($baseMapping['mapping_default_image'] as $channel_id => $channel_mapping)
        {
            foreach($channel_mapping as $attributeCode => $imageMapping)
            {
                $template = explode(".", $imageMapping);
                $mapping[$channel_id][$attributeCode][] = end($template);
            }
        }
        $this->addHardCodedMapping($mapping);
    }

    public function addHardCodedMapping($mapping)
    {
        $mapping = array_merge_recursive($mapping, $this->_hardcodedMapping);
        $this->_mapping = $mapping;
    }

    public function getProductImages($params)
    {
        $this->_parseRequestXml($params);
        return base64_encode($this->_response->asXml());
    }

    protected function _parseRequestXml($params)
    {
        $params = base64_decode($params);
        if (!empty($params))
        {
            $params = simplexml_load_string($params);
            if ( !empty($params->Styles->Style) )
            {
                $this->_startResponse();
                foreach($params->Styles->Style as $style)
                {
                    $this->_style_id = (string)$style['StyleId'];
                    $styleNode = $this->_styles->addChild('Style');
                    $styleNode->addAttribute('StyleId', $this->_style_id);
                    $channelsNode = $styleNode->addChild('Channels');

                    foreach($style->Channels->Channel as $channel)
                    {
                        $this->_channel_id = (string)$channel['ChannelId'];
                        $rcm = array();
                        foreach($channel->RcmIndexes->RcmIndex as $rcmIndex)
                        {
                            $index = (string)$rcmIndex['RcmIndexId'];
                            $rcm[$index] = array(
                                'name'      => (string)$rcmIndex['Name'],
                                'is_unique' => (string)$rcmIndex['IsUnique'],
                                'index'     => (string)$rcmIndex['Index'],
                            );
                            if( !empty($rcmIndex->Attributes->Attribute) )
                            {
                                foreach($rcmIndex->Attributes->Attribute as $attribute)
                                {
                                    $rcm[$index]['attribute'][] = (string)$attribute;
                                }
                            }
                        }

                        $channelNode = $channelsNode->addChild('Channel');
                        $channelNode->addAttribute('ChannelId', $this->_channel_id);
                        $rcmNode = $channelNode->addChild('RcmIndexes');

                        $this->_getStyleImages($rcm, $rcmNode);
                    }
                }
            }
            else
            {
                $this->_response = $this->errorResponse('Input XML should contain at least one Styles->Style element');
            }
        }
        else
        {
            $this->_response = $this->errorResponse('No input XML given');
        }
    }

    protected function _getStyleImages($rcm,&$node)
    {
        $data = $this->_db->getOne($this->_db->getTable('service_style'), array('style_id' => $this->_style_id, 'channel_id' => $this->_channel_id), '*');
        if( !empty($data) )
        {
            $product = Mage::getModel('catalog/product')->load($data['internal_id']);
            $mediaGallery = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', self::GALLERY_ATTRIBUTE_CODE);
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);

            $rcmIndexImages = array();
            foreach($rcm as $rcmIndex => $element)
            {
                $addedOption = array();
                if( !empty($element['attribute']) && !empty($childProducts) )
                {
                    $this->_fillAttributeData($element['attribute']);

                    $imageAttribute = current($element['attribute']); // TODO multichecker
                    $attribute_code = Mage::getModel('teamwork_transfer/class_attribute')->getAttributeCodeByName($this->_attributeset[$imageAttribute]['code']);

                    foreach($childProducts as $childKey => $childProduct)
                    {
                        $option_internal_id = $childProducts[$childKey]->getData( $attribute_code );
                        $optionProductGuid = '';

                        foreach($this->_attributesetvalues[$imageAttribute] as $option)
                        {
                            if($option['internal_id'] == $option_internal_id)
                            {
                                $optionProductGuid = $option['attribute_value_id'];
                                break;
                            }
                        }

                        if( empty($optionProductGuid) )
                        {
                            $this->_error[] = "An option no {$optionProductGuid} is missing in the CHQ for attribute {$attribute_code}";
                            continue;
                        }

                        if( !$childProducts[$childKey]->getAddGalleryInformation() )
                        {
                            $childProducts[$childKey]->setAddGalleryInformation(true);
                            $imageData = Mage::getResourceModel('catalog/product_attribute_backend_media')->loadGallery($childProducts[$childKey], $mediaGallery->getBackend());
                            if( !empty($imageData) )
                            {
                                $childProducts[$childKey]->setMediaGallery(array('images' => $imageData));
                            }
                        }

                        if( array_search($optionProductGuid, $addedOption) === FALSE && $childProducts[$childKey]->getMediaGalleryImages() )
                        {
                            $addedOption[] = $optionProductGuid;
                            if( !empty($rcmIndexImages[$rcmIndex][$optionProductGuid]) )
                            {
                                $rcmIndexImages[$rcmIndex][$optionProductGuid] = array_merge_recursive( $rcmIndexImages[$rcmIndex][$optionProductGuid], $this->_getImages($childProducts[$childKey],$element,$optionProductGuid) );
                            }
                            else
                            {
                                $rcmIndexImages[$rcmIndex][$optionProductGuid] = $this->_getImages($childProducts[$childKey],$element,$optionProductGuid);
                            }
                        }
                    }
                }
                else
                {
                    if( $product->getMediaGalleryImages() )
                    {
                        if( !empty($rcmIndexImages[$rcmIndex]) )
                        {
                            $rcmIndexImages[$rcmIndex] = array_merge_recursive( $rcmIndexImages[$rcmIndex], $this->_getImages($product,$element) );
                        }
                        else
                        {
                            $rcmIndexImages[$rcmIndex] = $this->_getImages($product,$element);
                        }

                    }
                }
            }
            $this->_addAdditionlRcmIndex($rcmIndexImages);
            $this->_addImagesToXml($rcmIndexImages,$node,$imageAttribute);
        }
    }

    protected function _getImages($product, $rcm, $option_id='style')
    {
        $mappingFlag = false;
        foreach($this->_mapping[$this->_channel_id] as $attributeCode => $mediaTemplateIndexes)
        {
            if( in_array($rcm['index'], $mediaTemplateIndexes) )
            {
                $mappingFlag = true;
                $mappingImage = ($product->getData($attributeCode) && $product->getData($attributeCode) != 'no_selection') ? $product->getData($attributeCode) : null ;
            }
        }

        $return = array();
        foreach($product->getMediaGalleryImages() as $image)
        {
            if( !empty($mappingImage) && $mappingImage == $image['file'])
            {
                if( $this->_isImported($image,$option_id) )
                {
                    continue;
                }
                return array($image['file'] => $image['url']);
            }
            elseif( empty($mappingImage) && !$mappingFlag )
            {
                if( $this->_isImported($image,$option_id) )
                {
                    continue;
                }
                $return[$image['file']] = $image['url'];
            }
            else
            {
                $this->_productImages[$option_id][$image['file']] = $image['url'];
            }
        }
        return $return;
    }

    protected function _isImported($image,$option_id)
    {
        $array = !empty($this->_importedImages[$option_id]) ? $this->_importedImages[$option_id] : array();
        if( in_array($image['url'],$array) )
        {
            return true;
        }
        $this->_importedImages[$option_id][$image['file']] = $image['url'];
        return false;
    }

    protected function _addImagesToXml($rcmImages, &$node, $imageAttribute)
    {
        foreach($rcmImages as $rcmIndex => $rcmIndexImages)
        {
            $rcmIndexNode = $node->addChild('RcmIndex');
            if( !empty($rcmIndex) )
            {
                $rcmIndexIdNode = $rcmIndexNode->addChild('RcmIndexId',$rcmIndex);
            }
            else
            {
                $rcmIndexIdNode = $rcmIndexNode->addChild('RcmIndexId');
                $rcmIndexIdNode->addAttribute('xsi:nil', 'true', 'http://www.w3.org/2001/XMLSchema-instance');
            }
            $imagesNode = $rcmIndexNode->addChild('Images');



            foreach($rcmIndexImages as $optionGuid => $rcmIndexImage)
            {
                if( is_array($rcmIndexImage) )
                {
                    foreach($rcmIndexImage as $attribute => $image)
                    {
                        $imageNode = $imagesNode->addChild('Image');
                        $imageNode->addChild('ImageUrl', $image);
                        if( !empty($attribute) )
                        {
                            $attributesNode = $imageNode->addChild('Attributes');
                            $attributeNode = $attributesNode->addChild('Attribute', $optionGuid);
                            $attributeNode->addAttribute('AttributeId', $imageAttribute);
                        }
                    }
                }
                else
                {
                    $imageNode = $imagesNode->addChild('Image');
                    $imageNode->addChild('ImageUrl', $rcmIndexImage);
                }
            }
        }
    }

    protected function _addAdditionlRcmIndex(&$rcmIndexImages)
    {
        foreach($this->_productImages as $key => $additionalRcm)
        {
            if(is_array($additionalRcm))
            {
                foreach($additionalRcm as $image)
                {
                    $rcmIndexImages[0][] = $image;
                }
            }
            else
            {
                $rcmIndexImages[0][] = $additionalRcm;
            }

        }
    }

    protected function _fillAttributeData($element)
    {
        foreach($element as $attribute)
        {
            if( empty($this->_attributeset[$attribute]) )
            {

                $this->_attributeset[$attribute] = $this->_db->getOne($this->_db->getTable('service_attribute_set'), array('attribute_set_id' => $attribute), '*');
                $this->_attributesetvalues[$attribute] = $this->_db->getAll($this->_db->getTable('service_attribute_value'), array('attribute_set_id' => $attribute), '*');
            }
        }
    }

    protected function _startResponse()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>';
        $this->_response = new SimpleXMLElement($xml);
        $this->_styles = $this->_response->addChild('Styles');
    }

    public function errorResponse($error)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>';
        $response = new SimpleXMLElement($xml);
        $response->addAttribute('RequestId', $this->_request_id);
        $response->addChild('Status', 'Error');
        $errors = $response->addChild('Errors');
        $errors->addChild('Error', $error);
        return $response;
    }
}