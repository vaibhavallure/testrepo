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
class Ecp_Video_Adminhtml_VideoController extends Mage_Adminhtml_Controller_action
{
	private $videoFormat = array('flv','mp4','wmv','avi','mpg','mpeg');
        private $errors = array();

        protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('ecp_video/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ecp_video/video')->load($id);
                /*@var $model Ecp_Video_Model_Video */

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('video_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('ecp_video/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('ecp_video/adminhtml_video_edit'))
				->_addLeft($this->getLayout()->createBlock('ecp_video/adminhtml_video_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_video')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() 
	{
		$this->_forward('edit');
	}
 
	public function saveAction() 
	{            
            $id = $this->getRequest()->getParam('id');
            $videoModel = Mage::getModel('ecp_video/video')->load($id);           
            try{
            if ($data = $this->getRequest()->getPost()) {
            
                    $path = Mage::getBaseDir('media') . DS . 'videoupload' . DS;
                    $key = $videoModel->generateKey();
                    $ext = explode(".", $_FILES['thumbnail']['name']);
                    $name = $key.".".$ext[1];
                    $videoModel->setKey($key);
                    $videoModel->setTitle($data['title']);
                    $videoModel->setPosition($data['position']);
                    $videoModel->setDuration($data['duration']);
                    $videoModel->setDescription($data['description']);
                    $videoModel->setStatus($data['status']);
                    if(isset($data['thumbnail']['delete'])){
                        unlink(Mage::getBaseDir('media') . $data['thumbnail']['value']);
                        $videoModel->setThumbnail('');
                    }
                    if (!empty($_FILES['thumbnail']['name'])){
                            $videoModel->setThumbnail('/videoupload/'.$name);
                            $this->uploader($path,$name,'image');
                    }
                    if(strlen($data['url']) > 4){
                        if (strpos($data['url'], 'youtube.com') !== false) {
                            $data['url'] = str_replace('watch?v=', 'embed/', $data['url']);
                        }
                        $videoModel->setUrl($data['url']);
                    }else if($_FILES['video']['name'] != ''){                           
                            $videoext = explode(".", $_FILES['video']['name']);
                            $videoname = $key.".".$videoext[1];
                            $this->uploader($path,$videoname,'video');
                            $videoModel->setUrl('/media/videoupload/'.$videoname);
                    }
                                       
                    if(count($this->errors)){
                         $errorsMes = null;
                         foreach($this->errors as $mess){
                             $errorsMes = $mess. '<br>';                             
                         }
                         Mage::getSingleton('adminhtml/session')->addError($errorsMes);
                         Mage::getSingleton('adminhtml/session')->setVideoData($this->getRequest()->getPost());
                         $this->_redirect('*/*/edit/');
                         return;
                    }
                    $videoModel->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_video')->__('Item was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);

                    // check if 'Save and Continue'
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $videoModel->getId()));
                        return;
                    }

                    $this->_redirect('*/*/');
                    return;
            }
            }catch(Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_video')->__($e));
                    Mage::getSingleton('adminhtml/session')->setVideoData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit/');
            }
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('ecp_video/video');
                                /*@var $model Ecp_Video_Model_Video */
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $videoIds = $this->getRequest()->getParam('video');
        if(!is_array($videoIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($videoIds as $videoId) {
                    $video = Mage::getModel('ecp_video/video')->load($videoId);
                    /*@var $video Ecp_Video_Model_Video */
                    $video->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($videoIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $videoIds = $this->getRequest()->getParam('video');
        if(!is_array($videoIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($videoIds as $videoId) {
                    $video = Mage::getSingleton('ecp_video/video')
                        ->load($videoId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($videoIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'video.csv';
        $content    = $this->getLayout()->createBlock('ecp_video/adminhtml_video_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'video.xml';
        $content    = $this->getLayout()->createBlock('ecp_video/adminhtml_video_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }


    private function uploader($path,$name,$type)
    {          
    	switch ($type) {
    		case 'image':
                    try {
    			$uploader = new Varien_File_Uploader('thumbnail');
    			$uploader->setAllowedExtensions(array('jpeg','png','jpg','gif'));
    			$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$uploader->save($path, $name );
                    }catch(Exception $e){                            
                            $this->errors[] = $this->__("Allowed Extensions: 'jpeg', 'png', 'jpg', 'gif'!");
                    }
    			break;
    		case 'video':                    
    		try {
    			$uploader = new Varien_File_Uploader('video');
    			$uploader->setAllowedExtensions(array('mp4','flv','mpeg','wmv'));
    			$uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $uploader->save($path, $name );
		}catch(Exception $e){                            
                            $this->errors[] = $this->__("Allowed Extensions: 'mp4', 'flv', 'mpeg', 'wmv'!");
		}
    		break;
    	}
		
    }

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_video');
    }
}