<?php

class Allure_GoogleConnect_Block_Account extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $oauth2 = null;
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $model = Mage::getSingleton('allure_googleconnect/client');

        if(!($this->client = $model->getClient()) ||
                !($this->oauth2 = $model->getOauth2()))
                return;

        $this->userInfo = Mage::registry('allure_googleconnect_userinfo');

        $this->setTemplate('allure/googleconnect/account.phtml');
    }

    protected function _hasUserInfo()
    {
        return (bool) $this->userInfo;
    }

    protected function _getGoogleId()
    {
        return $this->userInfo['id'];
    }

    protected function _getStatus()
    {
        if(!empty($this->userInfo['link'])) {
            $link = '<a href="'.$this->userInfo['link'].'" target="_blank">'.
                    $this->htmlEscape($this->userInfo['name']).'</a>';
        } else {
            $link = $this->userInfo['name'];
        }

        return $link;
    }

    protected function _getEmail()
    {
        return $this->userInfo['email'];
    }

    protected function _getPicture()
    {
        if(!empty($this->userInfo['picture'])) {
            return Mage::helper('allure_googleconnect')
                    ->getProperDimensionsPictureUrl($this->userInfo['id'],
                            $this->userInfo['picture']);
        }

        return null;
    }

    protected function _getGender()
    {
        if(!empty($this->userInfo['gender'])) {
            return ucfirst($this->userInfo['gender']);
        }

        return null;
    }

    protected function _getBirthday()
    {
        if(!empty($this->userInfo['birthday'])) {
            if((strpos($this->userInfo['birthday'], '0000')) === false) {
                $birthday = date('F j, Y', strtotime($this->userInfo['birthday']));
            } else {
                $birthday = date(
                    'F j',
                    strtotime(
                        str_replace('0000', '1970', $this->userInfo['birthday'])
                    )
                );
            }

            return $birthday;
        }

        return null;
    }

}
