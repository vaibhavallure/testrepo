<?php

class Allure_Virtualstore_Adminhtml_VirtualstoresController extends Mage_Adminhtml_Controller_Action
{
   /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        $this->_setForcedFormKeyActions(array('deleteWebsitePost', 'deleteGroupPost', 'deleteStorePost'));
        return parent::preDispatch();
    }

    /**
     * Init actions
     *
     * @return Mage_Adminhtml_Cms_PageController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('allure/store_virtualstores')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('allure'), Mage::helper('adminhtml')->__('Allure'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Virtual Stores'), Mage::helper('adminhtml')->__('Manage Virtual Stores'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Allure'))
             ->_title($this->__('Stores'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_store'))
            ->renderLayout();
    }

    public function newWebsiteAction()
    {
        Mage::register('store_type', 'website');
        $this->_forward('newStore');
    }

    public function newGroupAction()
    {
        Mage::register('store_type', 'group');
        $this->_forward('newStore');
    }

    public function newStoreAction()
    {
        if (!Mage::registry('store_type')) {
            Mage::register('store_type', 'store');
        }
        Mage::register('store_action', 'add');
        $this->_forward('editStore');
    }

    public function editWebsiteAction()
    {
        Mage::register('store_type', 'website');
        $this->_forward('editStore');
    }

    public function editGroupAction()
    {
        Mage::register('store_type', 'group');
        $this->_forward('editStore');
    }

    public function editStoreAction()
    {
        $this->_title($this->__('Allure'))
             ->_title($this->__('Stores'));

        $session = $this->_getSession();
        if ($session->getPostData()) {
            Mage::register('store_post_data', $session->getPostData());
            $session->unsPostData();
        }
        if (!Mage::registry('store_type')) {
            Mage::register('store_type', 'store');
        }
        if (!Mage::registry('store_action')) {
            Mage::register('store_action', 'edit');
        }
        switch (Mage::registry('store_type')) {
            case 'website':
                $itemId     = $this->getRequest()->getParam('website_id', null);
                $model      = Mage::getModel('allure_virtualstore/website');
                $title      = Mage::helper('allure_virtualstore')->__("Website");
                $notExists  = Mage::helper('allure_virtualstore')->__("The website does not exist.");
                $codeBase   = Mage::helper('allure_virtualstore')->__('Before modifying the website code please make sure that it is not used in index.php.');
                break;
            case 'group':
                $itemId     = $this->getRequest()->getParam('group_id', null);
                $model      = Mage::getModel('allure_virtualstore/group');
                $title      = Mage::helper('allure_virtualstore')->__("Store");
                $notExists  = Mage::helper('allure_virtualstore')->__("The store does not exist");
                $codeBase   = false;
                break;
            case 'store':
                $itemId     = $this->getRequest()->getParam('store_id', null);
                $model      = Mage::getModel('allure_virtualstore/store');
                $title      = Mage::helper('allure_virtualstore')->__("Store View");
                $notExists  = Mage::helper('allure_virtualstore')->__("Store view doesn't exist");
                $codeBase   = Mage::helper('allure_virtualstore')->__('Before modifying the store view code please make sure that it is not used in index.php.');
                break;
        }
        if (null !== $itemId) {
            $model->load($itemId);
        }

        if ($model->getId() || Mage::registry('store_action') == 'add') {
            Mage::register('store_data', $model);

            if (Mage::registry('store_action') == 'add') {
                $this->_title($this->__('New ') . $title);
            }
            else {
                $this->_title($model->getName());
            }

            if (Mage::registry('store_action') == 'edit' && $codeBase && !$model->isReadOnly()) {
                $this->_getSession()->addNotice($codeBase);
            }

            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_edit'))
                ->renderLayout();
        }
        else {
            $session->addError($notExists);
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPost()) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $this->_redirect('*/*/');
                return;
            }
            $session = $this->_getSession();

            try {
                switch ($postData['store_type']) {
                    case 'website':
                        $postData['website']['name'] = $this->_getHelper()->removeTags($postData['website']['name']);
                        $websiteModel = Mage::getModel('allure_virtualstore/website');
                        if ($postData['website']['website_id']) {
                            $websiteModel->load($postData['website']['website_id']);
                        }
                        $websiteModel->setData($postData['website']);
                        if ($postData['website']['website_id'] == '') {
                            $websiteModel->setId(null);
                        }

                        $websiteModel->save();
                        $session->addSuccess(Mage::helper('allure_virtualstore')->__('The website has been saved.'));
                        break;

                    case 'group':
                        $postData['group']['name'] = $this->_getHelper()->removeTags($postData['group']['name']);
                        $groupModel = Mage::getModel('allure_virtualstore/group');
                        if ($postData['group']['group_id']) {
                            $groupModel->load($postData['group']['group_id']);
                        }
                        $groupModel->setData($postData['group']);
                        if ($postData['group']['group_id'] == '') {
                            $groupModel->setId(null);
                        }

                        $groupModel->save();

                        //Mage::dispatchEvent('store_group_save', array('group' => $groupModel));

                        $session->addSuccess(Mage::helper('allure_virtualstore')->__('The store has been saved.'));
                        break;

                    case 'store':
                        $eventName = 'store_edit';
                        $storeModel = Mage::getModel('allure_virtualstore/store');
                        $postData['store']['name'] = $this->_getHelper()->removeTags($postData['store']['name']);
                        if ($postData['store']['store_id']) {
                            $storeModel->load($postData['store']['store_id']);
                        }
                        $storeModel->setData($postData['store']);
                        if ($postData['store']['store_id'] == '') {
                            $storeModel->setId(null);
                            $eventName = 'store_add';
                        }
                        $groupModel = Mage::getModel('allure_virtualstore/group')->load($storeModel->getGroupId());
                        $storeModel->setWebsiteId($groupModel->getWebsiteId());
                        $storeModel->save();

                        //Mage::app()->reinitStores();

                       //Mage::dispatchEvent($eventName, array('store'=>$storeModel));

                        $session->addSuccess(Mage::helper('allure_virtualstore')->__('The store view has been saved'));
                        break;
                    default:
                        $this->_redirect('*/*/');
                        return;
                }
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $session->setPostData($postData);
            }
            catch (Exception $e) {
                $session->addException($e, Mage::helper('allure_virtualstore')->__('An error occurred while saving. Please review the error log.'));
                $session->setPostData($postData);
            }
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/');
    }

    public function deleteWebsiteAction()
    {
        $this->_title($this->__('Allure'))
             ->_title($this->__('Stores'))
             ->_title($this->__('Delete Website'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = Mage::getModel('allure_virtualstore/website')->load($itemId)) {
            $session->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again.'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(Mage::helper('allure_virtualstore')->__('This website cannot be deleted.'));
            $this->_redirect('*/*/editWebsite', array('website_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('website');

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('allure_virtualstore')->__('Delete Website'), Mage::helper('core')->__('Delete Website'))
            ->_addContent($this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_delete')
                ->setFormActionUrl($this->getUrl('*/*/deleteWebsitePost'))
                ->setBackUrl($this->getUrl('*/*/editWebsite', array('website_id' => $itemId)))
                ->setStoreTypeTitle(Mage::helper('allure_virtualstore')->__('Website'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteGroupAction()
    {
        $this->_title($this->__('Allure'))
             ->_title($this->__('Stores'))
             ->_title($this->__('Delete Store'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = Mage::getModel('allure_virtualstore/group')->load($itemId)) {
            $session->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again.'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(Mage::helper('allure_virtualstore')->__('This store cannot be deleted.'));
            $this->_redirect('*/*/editGroup', array('group_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('store');

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('allure_virtualstore')->__('Delete Store'), Mage::helper('core')->__('Delete Store'))
            ->_addContent($this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_delete')
                ->setFormActionUrl($this->getUrl('*/*/deleteGroupPost'))
                ->setBackUrl($this->getUrl('*/*/editGroup', array('group_id' => $itemId)))
                ->setStoreTypeTitle(Mage::helper('allure_virtualstore')->__('Store'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteStoreAction()
    {
        $this->_title($this->__('Allure'))
             ->_title($this->__('Stores'))
             ->_title($this->__('Delete Store View'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = Mage::getModel('allure_virtualstore/store')->load($itemId)) {
            $session->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again.'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(Mage::helper('allure_virtualstore')->__('This store view cannot be deleted.'));
            $this->_redirect('*/*/editStore', array('store_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('store view');;

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('allure_virtualstore')->__('Delete Store View'), Mage::helper('core')->__('Delete Store View'))
            ->_addContent($this->getLayout()->createBlock('allure_virtualstore/adminhtml_system_store_delete')
                ->setFormActionUrl($this->getUrl('*/*/deleteStorePost'))
                ->setBackUrl($this->getUrl('*/*/editStore', array('store_id' => $itemId)))
                ->setStoreTypeTitle(Mage::helper('allure_virtualstore')->__('Store View'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteWebsitePostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$model = Mage::getModel('allure_virtualstore/website')->load($itemId)) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('This website cannot be deleted.'));
            $this->_redirect('*/*/editWebsite', array('website_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editWebsite', array('website_id' => $itemId));

        try {
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('allure_virtualstore')->__('The website has been deleted.'));
            $this->_redirect('*/*/');
            return ;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('allure_virtualstore')->__('Unable to delete website. Please, try again later.'));
        }
        $this->_redirect('*/*/editWebsite', array('website_id' => $itemId));
    }

    public function deleteGroupPostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$model = Mage::getModel('allure_virtualstore/group')->load($itemId)) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again.'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('This store cannot be deleted.'));
            $this->_redirect('*/*/editGroup', array('group_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editGroup', array('group_id' => $itemId));

        try {
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('allure_virtualstore')->__('The store has been deleted.'));
            $this->_redirect('*/*/');
            return ;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('allure_virtualstore')->__('Unable to delete store. Please, try again later.'));
        }
        $this->_redirect('*/*/editGroup', array('group_id' => $itemId));
    }

    /**
     * Delete store view post action
     *
     */
    public function deleteStorePostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$model = Mage::getModel('allure_virtualstore/store')->load($itemId)) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('Unable to proceed. Please, try again'));
            $this->_redirect('*/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(Mage::helper('allure_virtualstore')->__('This store view cannot be deleted.'));
            $this->_redirect('*/*/editStore', array('store_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editStore', array('store_id' => $itemId));

        try {
            $model->delete();

            Mage::dispatchEvent('store_delete', array('store' => $model));

            $this->_getSession()->addSuccess(Mage::helper('allure_virtualstore')->__('The store view has been deleted.'));
            $this->_redirect('*/*/');
            return ;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('allure_virtualstore')->__('Unable to delete store view. Please, try again later.'));
        }
        $this->_redirect('*/*/editStore', array('store_id' => $itemId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('allure/store_virtualstores');
    }

    /**
     * Backup database
     *
     * @param string $failPath redirect path if backup failed
     * @param array $arguments
     * @return Mage_Adminhtml_System_StoreController
     */
    protected function _backupDatabase($failPath, $arguments=array())
    {
        if (! $this->getRequest()->getParam('create_backup')) {
            return $this;
        }
        try {
            $backupDb = Mage::getModel('backup/db');
            $backup   = Mage::getModel('backup/backup')
                ->setTime(time())
                ->setType('db')
                ->setPath(Mage::getBaseDir('var') . DS . 'backups');

            $backupDb->createBackup($backup);
            $this->_getSession()->addSuccess(Mage::helper('backup')->__('Database was successfuly backed up.'));
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect($failPath, $arguments);
            return ;
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('backup')->__('Unable to create backup. Please, try again later.'));
            $this->_redirect($failPath, $arguments);
            return ;
        }
        return $this;
    }

    /**
     * Add notification on deleting store / store view / website
     *
     * @param string $typeTitle
     * @return Mage_Adminhtml_System_StoreController
     */
    protected function _addDeletionNotice($typeTitle)
    {
        $this->_getSession()->addNotice(
            Mage::helper('allure_virtualstore')->__('Deleting a %1$s will not delete the information associated with the %1$s (e.g. categories, products, etc.), but the %1$s will not be able to be restored. It is suggested that you create a database backup before deleting the %1$s.', $typeTitle)
        );
        return $this;
    }

}

