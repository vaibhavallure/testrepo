<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * module base admin controller
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Controller_Adminhtml_AdvInstagram extends Mage_Adminhtml_Controller_Action
{
    /**
     * upload file and get the uploaded name
     *
     * @access public
     * @param string $input
     * @param string $destinationFolder
     * @param array $data
     * @return string
     */
    protected function _uploadAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = new Varien_File_Uploader($input);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return $result['file'];
            }
        } catch (Exception $e) {
            if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY) {
                throw $e;
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }
}
