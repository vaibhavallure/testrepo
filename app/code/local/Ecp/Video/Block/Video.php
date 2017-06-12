<?php

/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Video
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Video
 *
 * @category    Ecp
 * @package     Ecp_Video
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Video_Block_Video extends Mage_Core_Block_Template
{
    public $videosArray = array();
    public $videosJson = '';
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getVideos()     
     { 
        $videoCollection = Mage::getModel('ecp_video/video')->getCollection()->addFieldToFilter('status', 1);
        if(count($videoCollection) >= 1){
            $index = 0;
            foreach ($videoCollection as $key => $value) {
                $this->videosArray['video'][$index]['id'] = $key;
                $this->videosArray['video'][$index]['key'] = $value->getKey();
                $this->videosArray['video'][$index]['title'] = $value->getTitle();
                $this->videosArray['video'][$index]['duration'] = $value->getDuration();
                $this->videosArray['video'][$index]['thumbnail'] = $value->getThumbnail();
                $this->videosArray['video'][$index]['url'] = $value->getUrl();
                $this->videosArray['video'][$index]['description'] = $value->getDescription();
                $this->videosArray['video'][$index]['type'] = (stripos($value->getUrl(), $value->getKey()))?'file':'url';
                $index++;
            }
            $this->videosJson = json_encode($this->videosArray);
        }else{
            echo "there are not videos loaded";
        }       
    }

    public function videoView()
    {
        $videoKey = $this->getRequest()->getParam('keyvideo');
        if($videoKey){
            $videoModel = Mage::getModel('ecp_video/video')->load($videoKey);
            $key = (strpos($videoModel->getUrl(),'videoupload')) ? 'file' : 'url' ;
            return $videoModel->setType($key);
        }
        return false;
    }
}