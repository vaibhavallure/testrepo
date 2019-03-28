<?php

class Teamwork_CEGiftcards_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_REFUND_GIFTCARDS = 'teamwork_cegiftcards/general/refund_giftcards';

    const XML_PATH_CODE_FORMAT = 'teamwork_cegiftcards/general/code_format';
    const XML_PATH_CODE_LENGTH = 'teamwork_cegiftcards/general/code_length';
    const XML_PATH_CODE_SPLIT = 'teamwork_cegiftcards/general/code_split';
    const XML_PATH_CODE_SUFFIX = 'teamwork_cegiftcards/general/code_suffix';
    const XML_PATH_CODE_PREFIX = 'teamwork_cegiftcards/general/code_prefix';

    const XML_PATH_SEPARATOR = 'teamwork_cegiftcards/gc_code_generator_additional/separator';
    const XML_CHARSET_NODE = 'teamwork_cegiftcards/gc_code_generator_additional/charset/%s';

    const XML_PATH_EMAIL_TEMPLATE          = 'teamwork_cegiftcards/general/email_template';
    const XML_PATH_EMAIL_IDENTITY          = 'teamwork_cegiftcards/general/email_identity';
    const XML_PATH_EMAIL_COPY_TO           = 'teamwork_cegiftcards/general/email_copy_to';
    const XML_PATH_EMAIL_COPY_TO_GC_SENDER = 'teamwork_cegiftcards/general/email_copy_to_gc_sender';

    const XML_PATH_PIN_ENABLED = 'teamwork_cegiftcards/general/ena_pin_validation';
    const XML_PATH_PIN_GENERATION_ENABLED = 'teamwork_cegiftcards/general/ena_pin_generation';
    const XML_PATH_PIN_LENGHT = 'teamwork_cegiftcards/general/pin_length';
    const XML_CFG_PATH_PIN_CHARACTERS = 'teamwork_cegiftcards/pin_characters';

    const XML_PATH_MIN_OPEN_AMOUNT = 'teamwork_cegiftcards/import/min_open_amount';
    const XML_PATH_MAX_OPEN_AMOUNT = 'teamwork_cegiftcards/import/max_open_amount';

    const MAX_DOWNLOAD_ATTEMPTS = 3;

     protected $_ignoreCurlErrno = array(28);




    public function getClassConstVal($className, $constName, $defaultVal = null)
    {
        $ref = new ReflectionClass($className);
        $consts = $ref->getConstants();
        return isset($consts[$constName]) ? $consts[$constName] : $defaultVal;
    }

    public function getAppliedGiftcards(Mage_Sales_Model_Quote $quote)
    {
        return Mage::getModel('teamwork_cegiftcards/giftcard_link')
                   ->getCollection()
               ->addQuoteFilter($quote);
    }

    public function applyGC2Quote($quote, $gcCode, $gcPin)
    {
        $result = array(
            'error_msgs' => array(),
            'success_msgs' => array()
        );

        if ($gcCode !== false) {
            $appliedGCs = $this->getAppliedGiftcards($quote)->addGCFilter($gcCode);
            if ($appliedGCs->count() > 0) {
                $result['error_msgs'][] = $this->__("This gift card account is already in the quote.");
            } else {
                $svs = Mage::getModel('teamwork_cegiftcards/svs');
                try {
                    $gcData = $svs->getGiftcardData($gcCode, $gcPin);
                    if (!$gcData['active']) {
                        $result['error_msgs'][] = $this->__('Giftcard "%s" doesn\'t exists', Mage::helper('core')->escapeHtml($gcCode));
                    } else {
                        if ($gcData['giftcard_balance'] > 0) {
                            $gc = Mage::getModel("teamwork_cegiftcards/giftcard_link");
                            $gc->setData('quote_id', $quote->getId());
                            $gc->setData('gc_code', $gcCode);
                            $gc->setData('balance', $gcData['giftcard_balance']);
                            if (Mage::helper('teamwork_cegiftcards')->pinIsEnabled()) {
                                $gc->setData('gc_pin', $gcPin);
                            }
                            $position = 0;
                            $alreadyAppliedGCs = $this->getAppliedGiftcards($quote);
                            if ($alreadyAppliedGCs->count()) {
                                $position = $alreadyAppliedGCs->getLastItem()->getData('position') + 1;
                            }
                            $gc->setData('position', $position);
                            try {
                                $gc->save();
                            } catch (Exception $e) {
                                $result['error_msgs'][] = $this->__('Cannot apply gift card. Internal error occured. Please try later.');
                            }

                            $quote->setTotalsCollectedFlag(false);
                            $quote->collectTotals()->save();

                            $result['success_msgs'][] = $this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($gcCode));

                        } else {
                            $result['error_msgs'][] = $this->__('Cannot apply gift card. The gift card has zero balance.');
                        }
                    }
                } catch (Teamwork_CEGiftcards_Model_Exception $e) {
                    if ($e->isVisibleOnFrontend()) {
                        $msg = $e->getMessage();
                    } else {
                        $msg = $this->__('Cannot apply gift card. Internal error occured. Please try later.');
                    }
                    $result['error_msgs'][] = $msg;
                }
            }

        }

        return $result;
    }

    public function removeGCFromQuote($quote, $gcCode)
    {
        $result = array(
            'error_msgs' => array(),
            'success_msgs' => array()
        );
        $gcs = $this->getAppliedGiftcards($quote)->addGCFilter($gcCode);
        if ($gcs->count() > 0) {
            try {
                foreach($gcs as $gc) {
                    if (!$gc->getData('paid')) {
                        $gc->delete();
                    }
                }
                $result['success_msgs'][] = $this->__('Gift Card "%s" was removed.', Mage::helper('core')->escapeHtml($gcCode));
            } catch (Exception $e) {
                $result['error_msgs'][] = $this->__('Cannot remove gift card.');
            }
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save();
        }
        return $result;
    }

    public function sessionMsgsOut($session, $result)
    {
        foreach($result['error_msgs'] as $msg) {
            $session->addError($msg);
        }
        foreach($result['success_msgs'] as $msg) {
            $session->addSuccess($msg);
        }
    }


    public function saveBarCodeImage($code, $url)
    {
        $error = false;
        if (!empty($code) && !empty($url)) {

            $tempPath = (string)Mage::getConfig()->getNode("teamwork_cegiftcards/barcode_dir_temp");
            $tempPath = str_replace(array('\\', '/'), array(DS, DS), trim($tempPath, '\\/'));
            //$tempPath = Mage::getBaseDir('base') . DS . $tempPath . DS . $code;
            $tempPath = Mage::getBaseDir('tmp') . DS . $tempPath . DS . $code;

            //check/create temp dir
            $ioObject = new Varien_Io_File();
            $ioObject->setAllowCreateFolders(true);
            try {
                $ioObject->open(array('path' => dirname($tempPath)));
            } catch (Exception $e) {
                Mage::helper('teamwork_cegiftcards/log')->addMessage("Error occured while temp dir checking/creation ($code , $url): " . $e->getMessage());
                $error = true;
            }

            if (!$error) {
                $ioObject->close();
                $fileContent = $this->_getFileContent($url);
                if ($fileContent === false) {
                    $error = true;
                    Mage::helper('teamwork_cegiftcards/log')->addMessage("Error occured while downloading barcode image ($code , $url)");
                }
            }

            if (!$error) {
                $error |= (file_put_contents($tempPath, $fileContent) === false);
                if ($error) {
                    Mage::helper('teamwork_cegiftcards/log')->addMessage("Error occured while saving temp image ($code , $url)");
                }
            }

            $fileExtension = false;
            if (!$error) {
                $info = getimagesize($tempPath);
                if(!empty($info[2])) {
                    switch($info[2]) {
                        case IMAGETYPE_GIF:
                            $fileExtension = 'gif';
                            break;
                        case IMAGETYPE_JPEG:
                            $fileExtension = 'jpg';
                            break;
                        case IMAGETYPE_PNG:
                            $fileExtension = 'png';
                            break;
                        case IMAGETYPE_BMP:
                            $fileExtension = 'bmp';
                            break;
                    }
                    $error |= ($fileExtension === false);
                } else {
                    $error = true;
                }
                if ($error) {
                    Mage::helper('teamwork_cegiftcards/log')->addMessage("Wrong barcode file format ($code , $url)");
                }
            }
            $returnPath = false;
            if (!$error) {
                $path = (string)Mage::getConfig()->getNode("teamwork_cegiftcards/barcode_dir");
                $path = str_replace(array('\\', '/'), array(DS, DS), trim($path, '\\/'));
                //$path = Mage::getBaseDir('base') . DS . $path . DS . $code . "." . $fileExtension;
                $path = Mage::getBaseDir('media') . DS . $path . DS . $code . "." . $fileExtension;
                try {
                    //check/create dest dir
                    $ioObject->open(array('path' => dirname($path)));
                    //move image from temp dir
                    $ioObject->mv($tempPath, $path);
                    $ioObject->close();
                    $returnPath = (string)Mage::getConfig()->getNode("teamwork_cegiftcards/barcode_dir");
                    $returnPath = str_replace(array('\\', '/'), array(DS, DS), trim($returnPath, '\\/')) . DS . $code . "." . $fileExtension;
               } catch (Exception $e) {
                    Mage::helper('teamwork_cegiftcards/log')->addMessage("Error occured while moving temp image ($code , $url): " . $e->getMessage());
                    $error = true;
                }
            }
        } else {
            Mage::helper('teamwork_cegiftcards/log')->addMessage("Wrong arguments ($code , $url)");
            $error = true;
        }
        return $error ? false : $returnPath;
    }


    protected function _getFileContent($url)
    {
        $numAttemp = 0;
        $result = false;
        while($numAttemp < self::MAX_DOWNLOAD_ATTEMPTS) {
            $http = new Varien_Http_Adapter_Curl();
            $http->setConfig(array("header" => false));
            $http->addOption(CURLOPT_FOLLOWLOCATION, true);
            $http->write(Zend_Http_Client::GET, $url, 1.1);
            $result = $http->read();
            if($http->getError() && !in_array($http->getErrno(), $this->_ignoreCurlErrno)) {
                $result = false;
            } else {
                break;
            }
            $numAttemp++;
            $http->close();
        }
        return $result;
    }

    public function pinIsEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PIN_ENABLED);
    }

    public function pinGenerationIsEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PIN_GENERATION_ENABLED);
    }

}
