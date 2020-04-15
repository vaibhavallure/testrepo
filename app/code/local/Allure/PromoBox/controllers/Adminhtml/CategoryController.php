<?php

class Allure_PromoBox_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_action {


    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    function _isAllowed()
    {
        return true;
    }
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('promobox/category')->load($id);

        if ($model->getId() || $id == 0) {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            if($model->getId())
            Mage::register('category_data', $model);

            $this->loadLayout();

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('promobox/adminhtml_category_edit'))
                ->_addLeft($this->getLayout()->createBlock('promobox/adminhtml_category_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobox')->__('Category does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {


            $this->checkUpdates();

            $model = Mage::getModel('promobox/category');
            $data['id']=$this->getRequest()->getParam('id');
            $model->setData($data);
            try {
                $model->save();
                $this->saveBox($model->getId());

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promobox')->__('Category was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobox')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
    public function saveBox($promoboxCategoryId)
    {
        $data = $this->getRequest()->getPost();
        $model = Mage::getModel('promobox/box');

        foreach ($data['box'] as $box)
        {
            /*if(!$box['promobox_banner_id'])
                continue;*/

            $box['promobox_category_id']=$promoboxCategoryId;
            $model->setData($box);
            try {
                $model->save();
            }catch (Exception $e)
            {
                //$e->getMessage();
            }

        }

    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('promobox/category')->load($this->getRequest()->getParam('id'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Category was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $categoryIds = $this->getRequest()->getParam('category');
        if (!is_array($categoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Category'));
        } else {
            try {
                foreach ($categoryIds as $categoryId) {
                    $category = Mage::getModel('promobox/category')->load($categoryId);
                    $category->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d category(s) were successfully deleted', count($categoryIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function checkUpdates()
    {
        if(!$this->getRequest()->getParam('id'))
           return;

        $data = $this->getRequest()->getPost();

        $PromoCategory = Mage::getModel('promobox/category')->load($this->getRequest()->getParam('id'));

        if($PromoCategory->getCategoryId()!=$data['category_id'] || $PromoCategory->getStartingRow()!=$data['starting_row'] || $PromoCategory->getRowGap()!=$data['row_gap'] || $PromoCategory->getSize()!=$data['size'])
        {
            $this->clearBoxes();
        }
    }
    protected function clearBoxes()
    {
        $promoboxCategoryId=$this->getRequest()->getParam("id");
        $boxes= Mage::getModel("promobox/box")->getCollection()
            ->addFieldToFilter('promobox_category_id', array('eq'=>$promoboxCategoryId));

        foreach ($boxes as $box)
        {
            try{
                $box->delete();
            }catch (Exception $e)
            {
                //echo $e->getMessage();
            }
        }
    }

}