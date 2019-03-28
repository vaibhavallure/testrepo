<?php
class Teamwork_Transfer_Teamworktransfer_DamController extends Mage_Adminhtml_Controller_Action
{
    protected $_flags = array(
        '*' => array(
            Mage_Core_Controller_Varien_Action::FLAG_NO_CHECK_INSTALLATION => true,
            Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION => true,
            Mage_Core_Controller_Varien_Action::FLAG_NO_PRE_DISPATCH => true,
        )
    );

    public function _construct()
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        set_time_limit(0);
    }

    public function preDispatch()
    {
        Mage::getDesign()->setArea($this->_currentArea);
        $this->getLayout()->setArea($this->_currentArea);
        Mage_Core_Controller_Varien_Action::preDispatch();
        return $this;
    }

    public function getFlag($action, $flag='')
    {
        if (''===$action)
        {
            $action = $this->getRequest()->getActionName();
        }
        if (''===$flag)
        {
            return $this->_flags;
        }
        elseif (isset($this->_flags[$action][$flag]))
        {
            return $this->_flags[$action][$flag];
        }
        elseif (isset($this->_flags['*'][$flag]))
        {
            return $this->_flags['*'][$flag];
        }
        else
        {
            return false;
        }
    }

    public function updateAction()
    {
        if (!Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_IMAGES)) return;
        $marker = $this->getRequest()->getParam('dam_marker', false);
        if ($marker)
        {
            $damStyleCollection = Mage::getResourceModel('teamwork_service/dam_style_collection')
                    ->addCHQStylesData(true)
                    ->addCHQItemsData(true)
                ->addDAMMarkerFilter($marker);
            $classItemModel = Mage::getSingleton('teamwork_transfer/class_item');
            $classItemModel->init(array('channel_id'=>''));
            $classItemModel->initAttributeData();

            foreach($damStyleCollection as $damStyle)
            {
                $style = $damStyle->getData('chq_style_data');

                if (!empty($style['internal_id']))
                {
                    $product = Mage::getModel('catalog/product')->load($style['internal_id']);
                    if ($product->getId())
                    {
                        if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_STYLE))
                        {
                            try
                            {
                                $classItemModel->addProductImages($product, $style);
                                $product->save();
                                $classItemModel->saveImageInternalIds($product, $style);
                            }
                            catch (Exception $e)
                            {
                                Mage::helper('teamwork_transfer/log')->addException($e);
                            }
                        }

                        if ($classItemModel->getProductTypeByInventype($style['inventype']) == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                            && Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_ITEM))
                        {
                            $items = $damStyle->getData('chq_items');
                            foreach($items as $item)
                            {
                                $product = Mage::getModel('catalog/product')->load($item['internal_id']);
                                if ($product->getId())
                                {
                                    try
                                    {
                                        $classItemModel->addProductImages($product, $item);
                                        $product->save();
                                        $classItemModel->saveImageInternalIds($product, $item);
                                    }
                                    catch (Exception $e)
                                    {
                                        Mage::helper('teamwork_transfer/log')->addException($e);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
