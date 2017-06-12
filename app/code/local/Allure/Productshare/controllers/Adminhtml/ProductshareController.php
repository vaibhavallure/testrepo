<?php

class Allure_Productshare_Adminhtml_ProductshareController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed ()
    {
        // return
        // Mage::getSingleton('admin/session')->isAllowed('productshare/productshare');
        return true;
    }

    protected function _initAction ()
    {
        $this->loadLayout()
            ->_setActiveMenu("productshare/productshare")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("Productshare  Manager"), 
                Mage::helper("adminhtml")->__("Productshare Manager"));
        return $this;
    }

    public function indexAction ()
    {
        $this->_title($this->__("Productshare"));
        $this->_title($this->__("Manager Productshare"));
        
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction ()
    {
        $this->_title($this->__("Productshare"));
        $this->_title($this->__("Productshare"));
        $this->_title($this->__("Edit Item"));
        
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("productshare/productshare")->load($id);
        if ($model->getId()) {
            Mage::register("productshare_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("productshare/productshare");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productshare Manager"), 
                    Mage::helper("adminhtml")->__("Productshare Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productshare Description"), 
                    Mage::helper("adminhtml")->__("Productshare Description"));
            $this->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
                ->createBlock("productshare/adminhtml_productshare_edit"))
                ->_addLeft($this->getLayout()
                ->createBlock("productshare/adminhtml_productshare_edit_tabs"));
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("productshare")->__("Item does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction ()
    {
        $this->_title($this->__("Productshare"));
        $this->_title($this->__("Productshare"));
        $this->_title($this->__("New Item"));
        
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("productshare/productshare")->load($id);
        
        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }
        
        Mage::register("productshare_data", $model);
        
        $this->loadLayout();
        $this->_setActiveMenu("productshare/productshare");
        
        $this->getLayout()
            ->getBlock("head")
            ->setCanLoadExtJs(true);
        
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productshare Manager"), 
                Mage::helper("adminhtml")->__("Productshare Manager"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Productshare Description"), 
                Mage::helper("adminhtml")->__("Productshare Description"));
        
        $this->_addContent($this->getLayout()
            ->createBlock("productshare/adminhtml_productshare_edit"))
            ->_addLeft($this->getLayout()
            ->createBlock("productshare/adminhtml_productshare_edit_tabs"));
        
        $this->renderLayout();
    }

    public function saveAction ()
    {
        $post_data = $this->getRequest()->getPost();
        
        if ($post_data) {
            
            try {
                
                $status = $post_data['status'];
                $statusCode = Mage::helper("productshare")->getStatusCode($status);
                
                $post_data['status_code'] = $statusCode;
                if ($status == 3) {
                    // $post_data['last_updated_product'] = 0;
                    $post_data['execution'] = 0;
                }
                $model = Mage::getModel("productshare/productshare")->addData($post_data)
                    ->setId($this->getRequest()
                    ->getParam("id"))
                    ->save();
                
                Mage::getSingleton("adminhtml/session")->addSuccess(
                        Mage::helper("adminhtml")->__("Productshare was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setProductshareData(false);
                
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
                Mage::getSingleton("adminhtml/session")->setProductshareData($this->getRequest()
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

    public function deleteAction ()
    {
        if ($this->getRequest()->getParam("id") > 0) {
            try {
                $model = Mage::getModel("productshare/productshare");
                $model->setId($this->getRequest()
                    ->getParam("id"))
                    ->delete();
                Mage::getSingleton("adminhtml/session")->addSuccess(
                        Mage::helper("adminhtml")->__("Item was successfully deleted"));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array(
                        "id" => $this->getRequest()
                            ->getParam("id")
                ));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massRemoveAction ()
    {
        try {
            $ids = $this->getRequest()->getPost('ps_ids', array());
            foreach ($ids as $id) {
                $model = Mage::getModel("productshare/productshare");
                $model->setId($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction ()
    {
        $fileName = 'productshare.csv';
        $grid = $this->getLayout()->createBlock('productshare/adminhtml_productshare_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
        $fileName = 'productshare.xml';
        $grid = $this->getLayout()->createBlock('productshare/adminhtml_productshare_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
