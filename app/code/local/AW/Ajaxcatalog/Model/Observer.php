<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcatalog
 * @version    2.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcatalog_Model_Observer
{
    public function generateBlocksAfter($observer)
    {
        $request = Mage::app()->getRequest();
        if (!$request->getParam('aw_ajaxcatalog', false)) {
            return;
        }
        $layout = $observer->getEvent()->getLayout();
        $result = array(
            'success' => true
        );

        $containerSelector = $request->getParam(
            "aw_ajaxcatalog_selector", null
        );
        if (null === $containerSelector) {
            $result['success'] = false;
            $this->_sendResponse($result);
            return;
        }

        $html = $layout->getBlock('content')->toHtml();
        $simpleDOMHelper = Mage::helper('aw_ajaxcatalog/tools_simpledom');
        $dom = $simpleDOMHelper->str_get_html($html);

        $match = $dom->find($containerSelector);
        if (count($match) <= 0) {
            $result['success'] = false;
            $this->_sendResponse($result);
            return;
        }
        $element = array_pop($match);
        $result["content"] = $element->__toString();
        $this->_sendResponse($result);
    }

    public function coreLayoutUpdateUpdatesGetAfter($observer)
    {
        //set aw_ajaxcatalog node to the end of the list
        /* @var Mage_Core_Model_Config_Element $updateRoot */
        $updateRoot = $observer->getUpdates();
        $myLayoutName = "aw_ajaxcatalog";
        if (!$updateRoot->$myLayoutName) {
            return;
        }
        $element = clone $updateRoot->$myLayoutName;
        unset($updateRoot->$myLayoutName);
        $updateRoot->appendChild($element);
    }

    /**
     * @param $body
     */
    private function _sendResponse($body)
    {
        $response = Mage::app()->getResponse();
        $response->clearBody();
        $response->setHttpResponseCode(200);
        //remove location header from response
        $headers = $response->getHeaders();
        $response->clearHeaders();
        foreach ($headers as $header) {
            if ($header['name'] !== 'Location') {
                $response->setHeader(
                    $header['name'], $header['value'], $header['replace']
                );
            }
        }
        $response->sendHeaders();
        echo Zend_Json::encode($body);
        exit(0);
    }
}