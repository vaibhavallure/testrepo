<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:27 PM
 */
class Allure_Virtualstore_Adminhtml_VirtualstoreController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('allure_virtualstore/store');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Virtual Store"));
        $this->renderLayout();
    }
    public function editAction() {
        $this->_title ( $this->__ ( "Virtual Store Information" ) );
        $this->_title ( $this->__ ( "Edit Virtual Store" ) );

        $id = $this->getRequest ()->getParam ( "store_id" );
        $model = Mage::getModel ( "allure_virtualstore/store" )->load ( $id );
        if ($model->getId ()) {
            Mage::register ( "virtualstore_data", $model );
            $this->loadLayout ();
            $this->_setActiveMenu ( "allure_virtualstore/store" );
            $this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );
            $this->_addContent ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_store_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_store_edit_tabs" ) );
            $this->renderLayout ();
        } else {
            Mage::getSingleton ( "adminhtml/session" )->addError ( Mage::helper ( "allure_virtualstore" )->__ ( "Virtual Store does not exist." ) );
            $this->_redirect ( "*/*/" );
        }
    }
    public function newAction() {
        $this->_title ( $this->__ ( "Virtual Store" ) );

        $id = $this->getRequest ()->getParam ( "store_id" );
        $model = Mage::getModel ( "allure_virtualstore/store" )->load ( $id );

        $data = Mage::getSingleton ( "adminhtml/session" )->getFormData ( true );
        if (! empty ( $data )) {
            $model->setData ( $data );
        }

        Mage::register ( "store_data", $model );

        $this->loadLayout ();
        $this->_setActiveMenu ( "allure_virtualstore/store" );

        $this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );

        $this->_addContent ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_store_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_store_edit_tabs" ) );

        $this->renderLayout ();
    }
    public function saveAction() {

        $post_data = $this->getRequest()->getPost();
        if ($post_data) {

            try {

                $model = Mage::getModel('allure_virtualstore/store')->addData($post_data)
                    ->setId($this->getRequest()
                        ->getParam("store_id"))
                    ->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("adminhtml")->__("Item information saved sucessfully"));
                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array(
                        "store_id" => $model->getId()
                    ));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array(
                    "store_id" => $this->getRequest()
                        ->getParam("store_id")
                ));
                return;
            }
        }
        $this->_redirect("*/*/");
    }
    public function deleteAction() {
        if ($this->getRequest ()->getParam ( "store_id" ) > 0) {
            try {
                $model = Mage::getModel ( "allure_virtualstore/store" );
                $model->setId ( $this->getRequest ()->getParam ( "store_id" ) )->delete ();
                Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Virtual Store was successfully deleted" ) );
                $this->_redirect ( "*/*/" );
            } catch ( Exception $e ) {
                Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
                $this->_redirect ( "*/*/edit", array (
                    "store_id" => $this->getRequest ()->getParam ( "store_id" )
                ) );
            }
        }
        $this->_redirect ( "*/*/" );
    }

    public function massRemoveAction() {
        try {
            $ids = $this->getRequest ()->getPost ( 'store_ids', array () );
            foreach ( $ids as $id ) {
                $model = Mage::getModel ( "allure_virtualstore/store" );
                $model->setId ( $id )->delete ();
            }
            Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Virtual Store was successfully removed" ) );
        } catch ( Exception $e ) {
            Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
        }
        $this->_redirect ( '*/*/' );
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'virtualstore.csv';
        $grid = $this->getLayout ()->createBlock ( 'allure_virtualstore/adminhtml_store_grid' );
        $this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
    }
    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName = 'virtualstore.xml';
        $grid = $this->getLayout ()->createBlock ( 'allure_virtualstore/adminhtml_store_grid' );
        $this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
    }

}