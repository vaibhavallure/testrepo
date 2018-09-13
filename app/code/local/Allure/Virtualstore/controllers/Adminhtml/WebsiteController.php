<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:56 PM
 */
class Allure_Virtualstore_Adminhtml_WebsiteController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('allure_virtualstore/website');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Website"));
        $this->renderLayout();
    }
    public function editAction() {
        $this->_title ( $this->__ ( "Website Information" ) );
        $this->_title ( $this->__ ( "Edit Website" ) );

        $id = $this->getRequest ()->getParam ( "website_id" );
        $model = Mage::getModel ( "allure_virtualstore/website" )->load ( $id );
        if ($model->getId ()) {
            Mage::register ( "website_data", $model );
            $this->loadLayout ();
            $this->_setActiveMenu ( "allure_virtualstore/website" );
            $this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );
            $this->_addContent ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_website_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_website_edit_tabs" ) );
            $this->renderLayout ();
        } else {
            Mage::getSingleton ( "adminhtml/session" )->addError ( Mage::helper ( "allure_virtualstore" )->__ ( "Website does not exist." ) );
            $this->_redirect ( "*/*/" );
        }
    }
    public function newAction() {
        $this->_title ( $this->__ ( "Website" ) );

        $id = $this->getRequest ()->getParam ( "website_id" );
        $model = Mage::getModel ( "allure_virtualstore/website" )->load ( $id );

        $data = Mage::getSingleton ( "adminhtml/session" )->getFormData ( true );
        if (! empty ( $data )) {
            $model->setData ( $data );
        }

        Mage::register ( "website_data", $model );

        $this->loadLayout ();
        $this->_setActiveMenu ( "allure_virtualstore/website" );

        $this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );

        $this->_addContent ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_website_edit" ) )->_addLeft ( $this->getLayout ()->createBlock ( "allure_virtualstore/adminhtml_website_edit_tabs" ) );

        $this->renderLayout ();
    }
    public function saveAction() {

        $post_data = $this->getRequest()->getPost();
        if ($post_data) {

            try {

                $model = Mage::getModel('allure_virtualstore/website')->addData($post_data)
                    ->setId($this->getRequest()
                        ->getParam("website_id"))
                    ->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("adminhtml")->__("Item information saved sucessfully"));
                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array(
                        "website_id" => $model->getId()
                    ));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array(
                    "website_id" => $this->getRequest()
                        ->getParam("website_id")
                ));
                return;
            }
        }
        $this->_redirect("*/*/");
    }
    public function deleteAction() {
        if ($this->getRequest ()->getParam ( "website_id" ) > 0) {
            try {
                $model = Mage::getModel ( "allure_virtualstore/website" );
                $model->setId ( $this->getRequest ()->getParam ( "website_id" ) )->delete ();
                Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Website was successfully deleted" ) );
                $this->_redirect ( "*/*/" );
            } catch ( Exception $e ) {
                Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
                $this->_redirect ( "*/*/edit", array (
                    "website_id" => $this->getRequest ()->getParam ( "website_id" )
                ) );
            }
        }
        $this->_redirect ( "*/*/" );
    }

    public function massRemoveAction() {
        try {
            $ids = $this->getRequest ()->getPost ( 'website_ids', array () );
            foreach ( $ids as $id ) {
                $model = Mage::getModel ( "allure_virtualstore/website" );
                $model->setId ( $id )->delete ();
            }
            Mage::getSingleton ( "adminhtml/session" )->addSuccess ( Mage::helper ( "adminhtml" )->__ ( "Website was successfully removed" ) );
        } catch ( Exception $e ) {
            Mage::getSingleton ( "adminhtml/session" )->addError ( $e->getMessage () );
        }
        $this->_redirect ( '*/*/' );
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'website.csv';
        $grid = $this->getLayout ()->createBlock ( 'allure_virtualstore/adminhtml_website_grid' );
        $this->_prepareDownloadResponse ( $fileName, $grid->getCsvFile () );
    }
    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName = 'website.xml';
        $grid = $this->getLayout ()->createBlock ( 'allure_virtualstore/adminhtml_website_grid' );
        $this->_prepareDownloadResponse ( $fileName, $grid->getExcelFile ( $fileName ) );
    }

}