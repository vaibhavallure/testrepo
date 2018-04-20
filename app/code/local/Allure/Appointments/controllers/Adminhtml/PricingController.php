<?php

class Allure_Appointments_Adminhtml_PricingController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed ()
    {
        return true;
    }

    protected function _initAction ()
    {
        $this->loadLayout()
            ->_setActiveMenu("appointments/pricing")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("Piercing price Manager"), 
                Mage::helper("adminhtml")->__("Piercing price Manager"));
        return $this;
    }

    public function indexAction ()
    {
        $this->_title($this->__("Piercing Price"));
        $this->_title($this->__("Piercing price Manager"));
        
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_pricing'));
        $this->renderLayout();
    }

    public function editAction ()
    {
        $this->_title($this->__("Piercing Price"));
        $this->_title($this->__("Piercing Price"));
        $this->_title($this->__("Edit Item"));
        
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("appointments/pricing")->load($id);
        if ($model->getId()) {
            Mage::register("pricing_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("appointments/pricing");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Price Manager"), 
                    Mage::helper("adminhtml")->__("Price Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Price Description"), 
                    Mage::helper("adminhtml")->__("Price Description"));
            $this->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
                ->createBlock("appointments/adminhtml_pricing_edit"))
                ->_addLeft($this->getLayout()
                ->createBlock("appointments/adminhtml_pricing_edit_tabs"));
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("appointments")->__("Item does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction ()
    {
        $this->_title($this->__("Pricing"));
        $this->_title($this->__("Pricing"));
        $this->_title($this->__("New Item"));
        
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("appointments/pricing")->load($id);
        
        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }
        
        Mage::register("pricing_data", $model);
        
        $this->loadLayout();
        $this->_setActiveMenu("appointments/pricing");
        
        $this->getLayout()
            ->getBlock("head")
            ->setCanLoadExtJs(true);
        
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pricing Manager"), 
                Mage::helper("adminhtml")->__("Pricing Manager"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pricing Description"), 
                Mage::helper("adminhtml")->__("Pricing Description"));
        
        $this->_addContent($this->getLayout()
            ->createBlock("appointments/adminhtml_pricing_edit"))
            ->_addLeft($this->getLayout()
            ->createBlock("appointments/adminhtml_pricing_edit_tabs"));
        
        $this->renderLayout();
    }

    public function saveAction ()
    {
        $post_data = $this->getRequest()->getPost();
        
        if ($post_data) {
            
            try {
                
                
                $model = Mage::getModel("appointments/pricing")->addData($post_data)
                    ->setId($this->getRequest()
                    ->getParam("id"))
                    ->save();
                
                Mage::getSingleton("adminhtml/session")->addSuccess(
                        Mage::helper("adminhtml")->__("Pricing was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setPricingData(false);
                
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
                Mage::getSingleton("adminhtml/session")->setPricingData($this->getRequest()
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
                $model = Mage::getModel("appointments/pricing");
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
            $ids = $this->getRequest()->getPost('price_ids', array());
            foreach ($ids as $id) {
                $model = Mage::getModel("appointments/pricing");
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
        $fileName = 'pricing.csv';
        $grid = $this->getLayout()->createBlock('appointments/adminhtml_pricing_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
        $fileName = 'pricing.xml';
        $grid = $this->getLayout()->createBlock('appointments/adminhtml_pricing_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
