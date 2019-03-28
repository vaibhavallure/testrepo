<?php
class Allure_Metadata_Adminhtml_MetadataController extends Mage_Adminhtml_Controller_Action {
	protected function _isAllowed() {
		return true;
	}
	protected function _initAction() {
		$this->loadLayout ()->_setActiveMenu ( "metadata/metadata" )->_addBreadcrumb ( Mage::helper ( "adminhtml" )->__ ( "Netterms  Manager" ), Mage::helper ( "adminhtml" )->__ ( "Netterms Manager" ) );
		return $this;
	}
	public function indexAction() {
		$this->_title ( $this->__ ( "Metadata" ) );
		
		$this->_initAction ();
		$this->renderLayout ();
	}
	public function editAction() {
		$this->_title ( $this->__ ( "Metadata Information" ) );
		$this->_title ( $this->__ ( "Edit Item" ) );
		
		$id = $this->getRequest ()->getParam ( "id" );
		$model = Mage::getModel ( "metadata/metadata" )->load ( $id );
		if ($model->getId ()) {
			Mage::register ( "metadata_data", $model );
			$this->loadLayout ();
			$this->_setActiveMenu ( "metadata/metadata" );
			$this->_addBreadcrumb ( Mage::helper ( "adminhtml" )->__ ( "Metadata Manager" ), Mage::helper ( "adminhtml" )->__ ( "Netterms Manager" ) );
			$this->_addBreadcrumb ( Mage::helper ( "adminhtml" )->__ ( "Metadata Description" ), Mage::helper ( "adminhtml" )->__ ( "Netterms Description" ) );
			$this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );
			$this->_addContent ( $this->getLayout ()->createBlock ( "metadata/adminhtml_metadata_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "metadata/adminhtml_metadata_edit_tabs" ) );
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( "adminhtml/session" )->addError ( Mage::helper ( "metadata" )->__ ( "Item does not exist." ) );
			$this->_redirect ( "*/*/" );
		}
	}
	public function newAction() {
		$this->_title ( $this->__ ( "Metadata" ) );
		
		$id = $this->getRequest ()->getParam ( "id" );
		$model = Mage::getModel ( "metadata/metadata" )->load ( $id );
		
		$data = Mage::getSingleton ( "adminhtml/session" )->getFormData ( true );
		if (! empty ( $data )) {
			$model->setData ( $data );
		}
		
		Mage::register ( "metadata_data", $model );
		
		$this->loadLayout ();
		$this->_setActiveMenu ( "metadata/metadata" );
		
		$this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );
		
		$this->_addBreadcrumb ( Mage::helper ( "adminhtml" )->__ ( "Metadata Manager" ), Mage::helper ( "adminhtml" )->__ ( "Netterms Manager" ) );
		$this->_addBreadcrumb ( Mage::helper ( "adminhtml" )->__ ( "Metadata Description" ), Mage::helper ( "adminhtml" )->__ ( "Netterms Description" ) );
		
		$this->_addContent ( $this->getLayout ()->createBlock ( "metadata/adminhtml_metadata_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "metadata/adminhtml_metadata_edit_tabs" ) );
		
		$this->renderLayout ();
	}
	public function saveAction() {
	    
	    $post_data = $this->getRequest()->getPost();
	    if ($post_data) {
	        
	        try {
	            
	            $model = Mage::getModel('metadata/metadata')->addData($post_data)
	            ->setId($this->getRequest()
	                ->getParam("id"))
	                ->save();
	                
	                Mage::getSingleton("adminhtml/session")->addSuccess(
	                    Mage::helper("adminhtml")->__("Meta information saved sucessfully"));
	                Mage::getSingleton("adminhtml/session")->setPiercingtimingData(false);
	                
	                if ($this->getRequest()->getParam("back")) {
	                    $this->_redirect("*/*/edit", array(
	                        "id" => $model->getId()
	                    ));
	                    return;
	                }
	                $this->_redirect("*/*/");
	                return;
	        } catch (Exception $e) {
	            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
	            Mage::getSingleton("adminhtml/session")->setPiercingtimingData($this->getRequest()
	                ->getPost());
	            $this->_redirect("*/*/edit", array(
	                "id" => $this->getRequest()
	                ->getParam("id")
	            ));
	            return;
	        }
	    }
	    $this->_redirect("*/*/");
	}
	public function deleteAction() {
		if ($this->getRequest ()->getParam ( "id" ) > 0) {
			try {
				$model = Mage::getModel ( "metadata/metadata" );
				$model->setId ( $this->getRequest ()->getParam ( "id" ) )->delete ();
				Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Item was successfully deleted" ) );
				$this->_redirect ( "*/*/" );
			} catch ( Exception $e ) {
				Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
				$this->_redirect ( "*/*/edit", array (
						"id" => $this->getRequest ()->getParam ( "id" ) 
				) );
			}
		}
		$this->_redirect ( "*/*/" );
	}
	public function massRemoveAction() {
		try {
			$ids = $this->getRequest ()->getPost ( 'ids', array () );
			foreach ( $ids as $id ) {
				$model = Mage::getModel ( "metadata/metadata" );
				$model->setId ( $id )->delete ();
			}
			Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Item(s) was successfully removed" ) );
		} catch ( Exception $e ) {
			Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
		}
		$this->_redirect ( '*/*/' );
	}
	
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction() {
		$fileName = 'metadata.csv';
		$grid = $this->getLayout ()->createBlock ( 'metadata/adminhtml_metadata_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
	}
	/**
	 * Export order grid to Excel XML format
	 */
	public function exportExcelAction() {
		$fileName = 'metadata.xml';
		$grid = $this->getLayout ()->createBlock ( 'metadata/adminhtml_metadata_grid' );
		$this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
	}
}
