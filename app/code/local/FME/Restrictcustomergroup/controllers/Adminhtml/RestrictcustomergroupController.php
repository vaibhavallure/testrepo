<?php
require_once 'AbstractblockController.php';
class FME_Restrictcustomergroup_Adminhtml_RestrictcustomergroupController
	extends FME_Restrictcustomergroup_Adminhtml_AbstractblockController {

	protected function _initAction() {
		
		$this->loadLayout()
			->_setActiveMenu('restrictcustomergroup/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	/**
	 * method responsible for generating childern for
	 * parent category taking in category id via post
	 * @return void
	 * */
	public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
	
	public function editAction() {
		
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('restrictcustomergroup/restrictcustomergroup')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('restrictcustomergroup_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('restrictcustomergroup/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rules Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Rule'), Mage::helper('adminhtml')->__('Item Rule'));

			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);

			$this->_addContent($this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit'))
				->_addLeft($this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('restrictcustomergroup')->__('Item Rule does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		
		//$this->loadLayout();
		//$this->renderLayout();
		$this->_forward('edit');
	}
 
	public function saveAction()
	{		
		if ($data = $this->getRequest()->getPost())
		{ //echo '<pre>';print_r($data);exit;		
			$_helper = Mage::helper('restrictcustomergroup');
			$data['customer_groups'] = implode(',', $data['customer_groups']);
			if (isset($data['cms_pages']) && $data['cms_pages'] != '') {
				$data['cms_pages']	= implode(',',$data['cms_pages']);
			}
			if (isset($data['other_pages']) && $data['other_pages'] != '')
			{		
				$data['other_pages']	= implode(',',$data['other_pages']);	
			}
			$model = Mage::getModel('restrictcustomergroup/restrictcustomergroup');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			try
			{	
				if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL)
				{	
					$model->setCreatedTime(now())
						->setUpdateTime(now());		
				}
				else
				{	
					$model->setUpdateTime(now());	
				}
				//echo '<pre>';print_r($data);exit;	
				$request = $this->getRequest();
				$rule = $request->getParam('rule');
				$type = '';
				
				if ($rule)
				{
					$type = 'basic';
					$cond = array();
					$rule['css'] = $_helper->updateChild($rule['css'], 'catalogrule/rule_condition_combine', 'restrictcustomergroup/rule_condition_combine');
					$conditions = $_helper->convertFlatToRecursive($rule, array('css'));
					//echo '<pre>';print_r($conditions);exit;
					if (is_array($conditions) && isset($conditions['css']) && isset($conditions['css']['css_conditions_fieldset']))
					{
						$cond['condition_serialized']['conditions'] = $conditions['css']['css_conditions_fieldset'];
					}
					else
					{
						$cond['condition_serialized']['conditions'] = array();
					}	
					//echo '<pre>';print_r($cond);exit;
					$model->setFormType($type);
					$model->setConditionSerialized($cond['condition_serialized']);
					$model->save();	
				}
				else
				{	
					$type = 'manual';
					$_arr = array();
					$url = $data['url'];
					// for array to serialize url manual
					$_arr = array_combine($url['from'], $url['to']); //echo '<pre>';print_r($_arr);exit;	
					$model->setFormType($type);
					$model->setManualUrlRedirect(serialize($_arr));
					$model->save();
				}
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('restrictcustomergroup')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId(), 'type' => $type));
					return;
				}
				
				$this->_redirect('*/*/');
				return;
			
            }
			catch (Exception $e)
			{	
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
		
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('restrictcustomergroup')->__('Unable to find rule to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('restrictcustomergroup/restrictcustomergroup');
				 
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
        $restrictcustomergroupIds = $this->getRequest()->getParam('restrictcustomergroup');
        if(!is_array($restrictcustomergroupIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($restrictcustomergroupIds as $restrictcustomergroupId) {
                    $restrictcustomergroup = Mage::getModel('restrictcustomergroup/restrictcustomergroup')->load($restrictcustomergroupId);
                    $restrictcustomergroup->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($restrictcustomergroupIds)
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
        $restrictcustomergroupIds = $this->getRequest()->getParam('restrictcustomergroup');
        if(!is_array($restrictcustomergroupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($restrictcustomergroupIds as $restrictcustomergroupId) { 
                    $restrictcustomergroup = Mage::getSingleton('restrictcustomergroup/restrictcustomergroup')
                        ->load($restrictcustomergroupId)
                        ->setStatus((int)$this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($restrictcustomergroupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'restrictcustomergroup.csv';
        $content    = $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'restrictcustomergroup.xml';
        $content    = $this->getLayout()->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_grid')
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

	protected function _initStaticBlocks() {
		
        $model = Mage::getModel('restrictcustomergroup/restrictcustomergroup');
        $id = (int) $this->getRequest()->getParam('id');
        
        if ($id) {
			
            $model->load($id);
        }
        
        Mage::register('current_static_blocks', $model);

        return $model;
    }
	
	public function staticBlocksAction() {
		
		$this->_initStaticBlocks();
		$this->loadLayout(); 
		$this->getLayout()
			->getBlock('blocks.grid')
			->setBlocksRelated(
				$this->getRequest()
					->getPost('blocks_related', null)
			); 
			
		$this->renderLayout();
	}


    public function gridAction() {
		
		$this->_initStaticBlocks();
		$blocksArr = array();
        $id = (int) $this->getRequest()->getParam('id');
		
        foreach (Mage::registry('current_static_blocks')->getBlocksRelated($id) as $block) {
            $blocksArr = $block["block_id"];
        }
		
        if (!empty($_POST['blocks_related'])) {
            array_push($_POST["blocks_related"], $blocksArr);
        }
		
        Mage::registry('current_static_blocks')
			->setBlocksRelated($blocksArr);
			
        $this->getResponse()->setBody(
                $this->getLayout()
					->createBlock('restrictcustomergroup/adminhtml_restrictcustomergroup_edit_tab_blocks')
						->toHtml()
        );
    }
    //
    /**
     * Get specified tab grid
     */
    public function gridOnlyAction() {
        echo 'Function ===> GridOnlyAction';
        $this->_initStaticBlocks();
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/restrictcustomergroup_edit_tab_blocks')
                        ->toHtml()
        );
    }
}
