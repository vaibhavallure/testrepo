<?php
class Ecp_Celebrities_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{

    protected $_matchedEntities = array(
        'ecp_celebrities_indexer' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            //Mage_Index_Model_Event::TYPE_MASS_ACTION
        )
    );


    public function getName()
    {
        return Mage::helper('ecp_celebrities')->__('Celebrities indexer');
    }


    public function getDescription()
    {
        return Mage::helper('ecp_celebrities')->__('Refresh celebrities URL');
    }


    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $dataObj = $event->getDataObject();
        if($event->getType() == Mage_Index_Model_Event::TYPE_SAVE)
            $event->addNewData('celebrities_indexer_update', $dataObj->getId());
        elseif($event->getType() == Mage_Index_Model_Event::TYPE_DELETE)
            $event->addNewData('celebrities_indexer_delete', $dataObj->getId());
        //elseif($event->getType() == Mage_Index_Model_Event::TYPE_MASS_ACTION)
        //    $event->addNewData('celebrities_indexer_mass', $dataObj->getProductIds());
    }


    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if(!empty($data['celebrities_indexer_update']))
            $this->_reindexById($data['celebrities_indexer_update']);
        elseif(!empty($data['celebrities_indexer_delete']))
            $this->_removeIndexByCelebriryId($data['celebrities_indexer_delete']);
    }


    public function reindexAll()
    {
        $collection = Mage::getModel('ecp_celebrities/celebrities')->getCollection();
        $urlModel = Mage::getModel('core/url_rewrite');

        foreach($collection AS $v) {
            try {
                $requestPath = $v->getUrl();
                Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);
                $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'celebrity/'.$v->getId());

                $urlModel->setIdPath('celebrity/'.$v->getId())
                    ->setTargetPath('celebrities/index/index/id/'.$v->getId())
                    ->setDescription($v->getName().' celebrity page')
                    ->setRequestPath($requestPath)
                    ->setIsSystem(0)
                    ->setStoreId(0);

                $urlRewrite = $alreadyExists->getFirstItem();
                if (!empty($urlRewrite)) $urlModel->setId($urlRewrite->getId());
                $urlModel->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        }
    }


    protected function _reindexById($id)
    {
        try {
            $urlModel = Mage::getModel('core/url_rewrite');
            $model = Mage::getModel('ecp_celebrities/celebrities')->load($id);
            $requestPath = $model->getUrl();
            Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);
            $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'celebrity/'.$model->getId());

            $urlModel->setIdPath('celebrity/'.$model->getId())
                ->setTargetPath('celebrities/index/index/id/'.$model->getId())
                ->setDescription($model->getName().' celebrity page')
                ->setRequestPath($requestPath)
                ->setIsSystem(0)
                ->setStoreId(0);

            $urlRewrite = $alreadyExists->getFirstItem();
            if (!empty($urlRewrite)) $urlModel->setId($urlRewrite->getId());
            $urlModel->save();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }


    protected function _removeIndexByCelebriryId($id)
    {
        try {
            $model = Mage::getModel('ecp_celebrities/celebrities')->load($id);
            Mage::getModel('core/url_rewrite')
                ->loadByRequestPath($model->getUrl())
                ->delete();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

}