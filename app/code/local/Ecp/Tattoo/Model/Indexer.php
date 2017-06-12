<?php
class Ecp_Tattoo_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{

    protected $_matchedEntities = array(
        'ecp_tattoo_indexer' => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            //Mage_Index_Model_Event::TYPE_MASS_ACTION
        )
    );


    public function getName()
    {
        return Mage::helper('ecp_tattoo')->__('Tattoo indexer');
    }


    public function getDescription()
    {
        return Mage::helper('ecp_tattoo')->__('Refresh tattoo artists URL');
    }


    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $dataObj = $event->getDataObject();
        if($event->getType() == Mage_Index_Model_Event::TYPE_SAVE)
            $event->addNewData('tattoo_indexer_update', $dataObj->getId());
        elseif($event->getType() == Mage_Index_Model_Event::TYPE_DELETE)
            $event->addNewData('tattoo_indexer_delete', $dataObj->getId());
        //elseif($event->getType() == Mage_Index_Model_Event::TYPE_MASS_ACTION)
        //    $event->addNewData('tattoo_indexer_mass', $dataObj->getProductIds());
    }


    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if(!empty($data['tattoo_indexer_update']))
            $this->_reindexById($data['tattoo_indexer_update']);
        elseif(!empty($data['tattoo_indexer_delete']))
            $this->_removeIndexByTattooId($data['tattoo_indexer_delete']);
    }


    public function reindexAll()
    {
        $collection = Mage::getModel('ecp_tattoo/tattoo_artist')->getCollection();
        $urlModel = Mage::getModel('core/url_rewrite');

        foreach($collection AS $v) {
            try {
                $requestPath = $v->getUrl();
                Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);
                $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'tattoos/'.$v->getId());

                $urlModel->setIdPath('tattoos/'.$v->getId())
                    ->setTargetPath('tattoo/index/index/id/'.$v->getId())
                    ->setDescription($v->getName().' artist page')
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
            $model = Mage::getModel('ecp_tattoo/tattoo_artist')->load($id);
            $requestPath = $model->getUrl();
            Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);
            $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'tattoos/'.$model->getId());

            $urlModel->setIdPath('tattoos/'.$model->getId())
                ->setTargetPath('tattoo/index/index/id/'.$model->getId())
                ->setDescription($model->getName().' artist page')
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


    protected function _removeIndexByTattooId($id)
    {
        try {
            $model = Mage::getModel('ecp_tattoo/tattoo_artist')->load($id);
            Mage::getModel('core/url_rewrite')
                ->loadByRequestPath($model->getUrl())
                ->delete();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

}