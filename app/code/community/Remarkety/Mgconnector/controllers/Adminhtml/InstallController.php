<?php

/**
 * Adminhtml Install controller
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Remarkety_Mgconnector_Adminhtml_InstallController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action
     *
     * @return Remarkety_Mgconnector_Adminhtml_InstallController
     */
    protected function _initAction()
    {
        $this
            ->loadLayout()
            ->_title($this->__('Remarkety'))
            ->_setActiveMenu('mgconnector');

        return $this;
    }

    /**
     * Reinstall action
     */
    public function reinstallAction()
    {
        $config = Mage::getModel('core/config');
        $config
            ->deleteConfig(Remarkety_Mgconnector_Model_Install::XPATH_INSTALLED)
            ->deleteConfig('remarkety/mgconnector/api_key')
            ->deleteConfig('remarkety/mgconnector/intervals')
            ->deleteConfig('remarkety/mgconnector/last_response_status')
            ->deleteConfig('remarkety/mgconnector/last_response_message');

        foreach (Mage::app()->getWebsites() as $_website) {
            foreach ($_website->getGroups() as $_group) {
                foreach ($_group->getStores() as $_store) {
                    $scope = $_store->getStoreId();
                    $config->deleteConfig(Remarkety_Mgconnector_Model_Install::XPATH_INSTALLED, 'stores', $scope);
                }
            }
        }

        Mage::app()->getCacheInstance()->cleanType('config');

        Mage::getSingleton('core/session')->unsRemarketyLastResponseMessage();
        Mage::getSingleton('core/session')->unsRemarketyLastResponseStatus();

        $this->_redirect('*/*/install');
    }

    /**
     * Installation action
     */
    public function installAction()
    {
        $mode = Mage::helper('mgconnector')->getMode();
		$this
			->_initAction()
			->_title($this->__($this->_getTitle($mode)));

        $this
            ->_addContent($this->getLayout()->createBlock(sprintf('mgconnector/adminhtml_install_%s', $mode)))
            ->_addAdditionalContent()
            ->renderLayout();
    }

    protected function _addAdditionalContent()
    {
        $mode = Mage::helper('mgconnector')->getMode();

        switch($mode) {
            case Remarkety_Mgconnector_Model_Install::MODE_WELCOME:
                $this->_addContent($this->getLayout()->createBlock('mgconnector/adminhtml_install_welcome_store'));
                break;
        }

        return $this;
    }

    /**
     * Return title
     *
     * @param   string $mode
     * @return  string
     */
    protected function _getTitle($mode) {
        return ucwords(str_replace('_', ' - ', $mode));
    }

    /**
     * Complete action
     */
    public function completeAction()
    {
        $redirectBack = true;

        if($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            try {
                $install = Mage::getModel('mgconnector/install')
                    ->setData($params['data']);

                switch($params['data']['mode']) {
                    case Remarkety_Mgconnector_Model_Install::MODE_INSTALL_CREATE:
                        $install->installByCreateExtension();
                        break;
                    case Remarkety_Mgconnector_Model_Install::MODE_INSTALL_LOGIN:
                        $install->installByLoginExtension();
                        break;
                    case Remarkety_Mgconnector_Model_Install::MODE_UPGRADE:
                        $install->upgradeExtension();
                        break;
                    case Remarkety_Mgconnector_Model_Install::MODE_COMPLETE:
                        $install->completeExtensionInstallation();
                        break;
                    default:
                        throw new Mage_Core_Exception('Selected mode can not be handled.');
                }

                $redirectBack = false;
            } catch(Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        if($redirectBack) {
            $mode = isset($params['data']['mode']) ? $params['data']['mode'] : null;
            $this->_redirect('*/install/install', array('mode' => $mode));
        } else {
            $this->_redirect('*/install/install', array('mode' => 'welcome'));
        }
    }
}
