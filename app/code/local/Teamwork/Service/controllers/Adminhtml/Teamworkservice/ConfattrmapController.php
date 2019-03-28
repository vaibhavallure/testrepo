<?php

class Teamwork_Service_Adminhtml_Teamworkservice_ConfattrmapController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_confattrmap');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('link_id', false);
        if ($id === false)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong request'));
            return $this->_redirect('*/*/index');
        }
        $model = Mage::getModel('teamwork_service/confattrmapprop')->load($id);
        if (!$model->getId())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong request'));
            return $this->_redirect('*/*/index');
        }

        Mage::register('model', $model);

        $this->loadLayout()->_setActiveMenu('teamwork_service');

        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_confattrmap_edit');
        $this->_addContent($contentBlock);

        if (Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Form::getAttributeAssignedConfProducts($model->getData('chq_internal_id'))->count() > 0)
        {
            $productGrid = $this->getLayout()->createBlock('teamwork_service/adminhtml_confattrmap_edit_gridcontainer');
            if (!Mage::app()->isSingleStoreMode()) {
                $switchBlock = $this->getLayout()->createBlock('adminhtml/store_switcher', 'store_switcher')->setUseConfirm(0);
                $productGrid->setChild('store_switcher', $switchBlock);
            }
            $this->_addContent($productGrid);
        }



        $this->renderLayout();
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('link_id', false);
        if ($id === false)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong request'));
            return $this->_redirect('*/*/index');
        }

        $model = Mage::getModel('teamwork_service/confattrmapprop')->load($id);
        if (!$model->getId())
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong request'));
            return $this->_redirect('*/*/index');
        }

        $canChangeAttributeAndIsActiveParams = (Teamwork_Service_Block_Adminhtml_Confattrmap_Edit_Form::getAttributeAssignedConfProducts($model->getData('chq_internal_id'))->count() == 0);

        $attributeId = $canChangeAttributeAndIsActiveParams ? $this->getRequest()->getParam('attribute_id', null) : $model->getData('chq_internal_id');
        $valuesMapping = $this->getRequest()->getParam('values_mapping', null);
        $isActive = $canChangeAttributeAndIsActiveParams ? $this->getRequest()->getParam('is_active', null) : $model->getData('is_active');

        if ($isActive !== null && $valuesMapping !== null && $attributeId !== null)
        {
            try
            {
                if ($canChangeAttributeAndIsActiveParams)
                {
                    if ($isActive && !$attributeId)
                    {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Magento attribute.'));
                        return $this->_redirect('*/*/edit', array('_current'=>true));
                    }
                    $model->setData('is_active', intval($isActive));
                    $model->setData('chq_internal_id', $attributeId);
                }

                $model->setData('values_mapping', $valuesMapping);

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mapping was saved successfully'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/edit', array('_current'=>true));
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('_current'=>true));
            }
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError($this->__('Wrong request'));
        $this->_redirect('*/*/index');
    }

    public function productMassDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        } else {
            if (!empty($productIds)) {
                try {
                    foreach ($productIds as $productId) {
                        $product = Mage::getSingleton('catalog/product')->load($productId);
                        //Mage::dispatchEvent('catalog_controller_product_delete', array('product' => $product));
                        $product->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($productIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $linkId = $this->getRequest()->getParam('link_id', false);
        if ($linkId === false) $this->_redirect('*/*/index');
        else $this->_redirect('*/*/edit', array('_current'=>true));
    }


    public function productGridAction()
    {
        $id = $this->getRequest()->getParam('link_id', false);
        if ($id == false) $this->_redirect('*/*/index');
        $model = Mage::getModel('teamwork_service/confattrmapprop')->load($id);
        if (!$model->getId()) $this->_redirect('*/*/index');
        Mage::register('model', $model);
        $productGrid = $this->getLayout()->createBlock('teamwork_service/adminhtml_confattrmap_edit_gridcontainer_grid');
            $this->getResponse()
            ->setHeader('Content-Type', 'text/html')
            ->setBody($productGrid->toHTML());
    }
    
    protected function _isAllowed(){
        return true;
    }
}
