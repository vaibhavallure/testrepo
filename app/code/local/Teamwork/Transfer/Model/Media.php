<?php
/**
 * Media model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Media extends Teamwork_Transfer_Model_Abstract
{
    /*RCM server url setting name ("service_settings" table, "setting_name" column) */
    const MEDIA_SETTING = 'RcmSiteUrl';

    /**
     * Database connection object
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * Cannels' media urls
     *
     * @var array
     */
    protected $_media_url = array();

    /**
     * Path to temp image folder
     *
     * @var string
     */
    protected $_dir;

    /**
     * Media sub type ("service_media" table, "media_sub_type" column); working variable
     *
     * @var string
     */
    protected $_subtype;

    /**
     * Forbidden symbols for file naming
     *
     * @var array
     */
    protected $_forbiddenSymbolsInFileName = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', ' ');

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_dir = Mage::helper('teamwork_service')->getTempDir() . 'images' . DS;
    }

    /**
     * Get URL to RCM server of the $channel_id
     *
     * @var string $channel_id
     *
     * @return string
     */
    public function getSiteMediaUrl($channel_id)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_settings'), array('setting_value'))
            ->where('setting_name = ?', self::MEDIA_SETTING)
        ->where('channel_id = ?', $channel_id);

        $this->_media_url[$channel_id] = $this->_db->fetchOne($select);
    }

    /**
     * Get info about all media for the current ECM
     *
     * @param  string $hostId
     * @param  string $hostType
     * @param  string $channel_id
     *
     * @return array
     */
    public function getMediaImagesInfo($hostId, $hostType, $channel_id)
    {
        $imagesInfo = $this->_getMediaImagesInfoCHQ($hostId, $hostType/*, $channel_id*/);
        if ($hostType == Teamwork_Service_Model_Mapping::CONST_STYLE)
        {
            $imagesInfo = array_merge($imagesInfo, $this->_getMediaImagesInfoDAM($hostId));
        }

        return $imagesInfo;
    }

    protected function _getMediaImagesInfoCHQ($hostId, $hostType/*, $channel_id*/)
    {
        $this->_subtype = 'Image';
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_media'))
            ->where('host_id = ?', $hostId)
            ->where('host_type = ?', $hostType)
            ->where('media_sub_type = ?', $this->_subtype)
            //->where('channel_id = ?', $channel_id)
        ->order('order');

        $result = $this->_db->fetchAll($select);
        if (!$result) $result = array();
        else $this->_attachMagentoImagesIds($result);
        return $result;
    }

    protected function _getMediaImagesInfoDAM($styleId, $styleNo = false)
    {
        $imagesInfo = array();
        if (empty($styleId) && empty($styleNo)) return $imagesInfo;

        $style = Mage::getSingleton('teamwork_service/dam_style');
        if (!empty($styleId)) $style->load($styleId, 'style_id');
        else $style->load($styleNo, 'style_no');

        if ($style->getId())
        {
            $images = $style->getData('images');
            foreach($images as $image)
            {
                $mediaAttributes = array();
                if ($image['base']) $mediaAttributes[] = 'image';
                if ($image['thumbnail']) $mediaAttributes[] = 'thumbnail';
                if ($image['small']) $mediaAttributes[] = 'small_image';

                $imagesInfo[] = array(
                    'media_id' => $image['media_id'],
                    'media_uri' => $image['file_name'],
                    'host_id' =>  $style->getData('style_id'),
                    'media_index' => -1,
                    'attribute1' => $image['attributevalue1'],
                    'direct_uri' => str_replace(' ', '%20', $image['url']),
                    'media_name' => $image['file_name'],
                    'media_type' => 'LargeImages',
                    'attribute2' => null,
                    'attribute3' => null,
                    'order' => $image['sort'],
                    'media_attributes' => $mediaAttributes,
                    'excluded' => $image['excluded'],
                    'label' => (isset($image['label']) && $image['label'] != 'None') ? $image['label'] : null
                );

            }
        }

        $this->_attachMagentoImagesIds($imagesInfo);
        return $imagesInfo;
    }

    protected function _attachMagentoImagesIds(&$imagesInfo)
    {
        if ($imagesInfo)
        {
            $internalIdTable = Mage::getSingleton('core/resource')->getTableName('service_media_value');
            foreach ($imagesInfo as &$imageInfo)
            {
                $select = $this->_db->select()
                    ->from($internalIdTable, array('internal_id', 'saved_media_name'))
                ->where('media_id = ?', $imageInfo['media_id']);

                $results = $this->_db->fetchAll($select);
                if (empty($results))
                {
                    $imageInfo['saved_media_name'] = null;
                    $imageInfo['internal_ids']     = array();
                }
                else
                {
                    $firstResult                   = reset($results);
                    $imageInfo['saved_media_name'] = $firstResult['saved_media_name'];
                    $imageInfo['internal_ids']     = Mage::helper('teamwork_transfer')->array_column($results, 'internal_id');
                }
            }
        }
    }



   /**
     * Download image to temp directory from RCM server and return info about it.
     * If $imagesToLoad param is set, loads only images from $imagesToLoad param (usually $imagesToLoad is a part of all images list obtained with getMediaImagesInfo function)
     *
     * @var string $hostId
     * @var string $hostType
     * @var string $channel_id
     * @var string $uniqueId
     * @var array  $imagesToLoad custom image set to load. If not set, function will load all images
     *
     * @return array
     */
    public function loadMediaImages($hostId, $hostType, $channel_id, $uniqueId = null, $imagesToLoad = null)
    {
        $images = array();
        $allMediaUri = array();
        $this->getSiteMediaUrl($channel_id);

        // If $imagesToLoad isn't set, we load all images
        $results = (isset($imagesToLoad)) ? $imagesToLoad : $this->getMediaImagesInfo($hostId, $hostType, $channel_id);

        for($i = 0, $j = count($results); $i < $j; $i++)
        {
            try
            {
                $fileUrl = $this->_getUrl($results[$i]['direct_uri'], $results[$i]['media_uri'], $channel_id);
            }
            catch(Exception $e)
            {
                $this->_getLogger()->addException($e);
                continue;
            }
            if($img = $this->_get_content($fileUrl))
            {
                if(file_put_contents($this->_dir . $results[$i]['media_uri'], $img))
                {
                    $name = '';
                    $info = getimagesize($this->_dir . $results[$i]['media_uri']);

                    if(!empty($info[2]))
                    {
                        $info = $info[2];
                    }
                    else
                    {
                        continue;
                    }

                    $sufix = '';
                    if(in_array($results[$i]['media_name'], $allMediaUri))
                    {
                        $sufix = "_{$i}";
                    }

                    switch($info)
                    {
                        case IMAGETYPE_GIF:
                            $format = 'gif';
                        break;

                        case IMAGETYPE_JPEG:
                            $format = 'jpg';
                        break;

                        case IMAGETYPE_PNG:
                            $format = 'png';
                        break;

                        case IMAGETYPE_BMP:
                            $format = 'bmp';
                        break;
                    }
                    if(strtolower(substr($results[$i]['media_name'], -4)) == '.'.$format)
                    {
                        $name = substr($results[$i]['media_name'], 0, -4) . $sufix . '.' . $format;
                    }
                    else
                    {
                        $name = "{$results[$i]['media_name']}{$sufix}.{$format}";
                    }

                    if(!empty($uniqueId))
                    {
                        $name = $uniqueId . '_' . $name;
                    }

                    $allMediaUri[] = $results[$i]['media_name'];

                    $name = str_replace($this->_forbiddenSymbolsInFileName, '_', $name);
                    if(!empty($name) && rename($this->_dir . $results[$i]['media_uri'], $this->_dir . $name))
                    {
                        $images[] = array(
                            'name'          => $name,
                            'media_uri'     => $results[$i]['media_uri'],
                            'media_name'    => $results[$i]['media_name'],
                            'media_index'   => $results[$i]['media_index'],
                            'type'          => $results[$i]['media_type'],
                            'attribute1'    => $results[$i]['attribute1'],
                            'attribute2'    => $results[$i]['attribute2'],
                            'attribute3'    => $results[$i]['attribute3'],
                            'order'         => $results[$i]['order'],
                            'link'          => $this->_dir . $name,
                            'format'        => $format
                        );
                    }
                    else
                    {
                        //TODO EXCEPTION
                        unlink($this->_dir . $results[$i]['media_uri']);
                    }
                }
            }
            else
            {
                $this->_getLogger()->addMessage("there was not a file on this url: " . $fileUrl);
            }
        }
        return $images;
    }

    /**
     * Download ALL media images to temp directory from RCM server and return info about it
     * Shortcut to 'loadMediaImages' function (for compability purposes)
     *
     * @var string $hostId
     * @var string $hostType
     * @var string $channel_id
     * @var string $uniqueId
     *
     * @return array
     */
    public function getMediaImages($hostId, $hostType, $channel_id, $uniqueId = null)
    {
        return $this->loadMediaImages($hostId, $hostType, $channel_id, $uniqueId);
    }

    /**
     * Get media text from RCM server
     *
     * @var string $hostId
     * @var string $hostType
     * @var string $channel_id
     *
     * @return array
     */
    public function getMediaTexts($hostId, $hostType, $channel_id, $item = null)
    {
        $this->_subtype = 'Text';
        $return = array();
        $this->getSiteMediaUrl($channel_id);
        if(!isset($this->_media_url[$channel_id]))
        {
            return;
        }

        $itemJoinAtrr = "";

        for($j = 1; $j <= 3; $j++)
        {
            if (isset($item['attribute'.$j.'_id']))
            {
                $itemJoinAtrr = $itemJoinAtrr.$item['attribute'.$j.'_id'];
            }
        }

        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_media'))
            ->where('host_id = ?', $hostId)
            ->where('host_type = ?', $hostType)
            ->where('channel_id = ?', $channel_id)
        ->where('media_sub_type = ?', $this->_subtype)
        ;

        $results = $this->_db->fetchAll($select);

        if(!empty($results))
        {
            foreach($results as $res)
            {
                $attrJoin = $res['attribute1'].$res['attribute2'].$res['attribute3'];

                if ($itemJoinAtrr != $attrJoin && !empty($attrJoin))
                {
                    continue;
                }
                try
                {
                    $fileUrl = $this->_getUrl($res['direct_uri'], $res['media_uri'], $channel_id);
                }
                catch(Exception $e)
                {
                    $this->_getLogger()->addException($e);
                    continue;
                }

                if($txt = $this->_get_content($fileUrl))
                {
                    $return[$res['media_index']] = $txt;
                }
            }
        }

        return $return;
    }

    /**
     * Get media text from RCM server
     *
     * @param string $channelId
     * @param string $media_id
     *
     * @return string
     */
    public function getMediaTextsByLink($media_id, $channelId)
    {
        return $this->_get_content($this->_media_url[$channelId] . $media_id);
    }

    /**
     * RCM server request wrapper
     *
     * @param string $url
     *
     * @return string
     */
    protected function _get_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    /**
     * Get url to external resource
     *
     * @param string $directUrl
     * @param string $mediaUri
     * @param string $channelId
     *
     * @return string
     */
    protected function _getUrl($directUrl, $mediaUri, $channelId)
    {
        if (empty($directUrl))
        {
            if (empty($this->_media_url[$channelId]))
            {
                throw new Exception("direct_uri was not set for " . $mediaUri . " media_uri and media url was not set for " . $channelId . " channel");
            }
            return $this->_media_url[$channelId] . $mediaUri;
        }
        return $directUrl;
    }
}
