<?php
class Teamwork_Universalcustomers_Model_Thrower
{
    const LOGIN_BASE_PAGE = 'customer/account/createpost';
    
    const ERROR_DUPLICATE_EMAIL = 7;
    const ERROR_DUPLICATE_PHONE = 8;
    const ADMIN_PAGE = 'adminhtml';

    protected $_defaultError = 'Unknown error. Please contact us or try again later';
    protected $_defaultAdminError = 'Bad response from SVS server, please watch log files';
    
    public function errorThrower( $error=array() )
    {
        $originalPath = Mage::helper('teamwork_universalcustomers')->getRouteName( Mage::app()->getRequest()->getOriginalPathInfo() );
        if( !empty($error['code']) && in_array($error['code'], array(self::ERROR_DUPLICATE_EMAIL, self::ERROR_DUPLICATE_PHONE)) )
        {
            $message = Mage::helper('core')->__($error['message']);
            if($error['code'] == self::ERROR_DUPLICATE_EMAIL && $originalPath == self::LOGIN_BASE_PAGE)
            {
                $message .= ". Please login using login page. If you forgot your password you can restore it using forget password page.";
            }
        }
        elseif( $this->isAdminPage() )
        {
            $message = !empty($error['message']) ? (Mage::helper('core')->__($error['message'])) : (Mage::helper('core')->__($this->_defaultAdminError));
        }
        else
        {
            $message = Mage::helper('core')->__($this->_defaultError);
        }

        if( $this->isAdminPage() )
        {
            $level = new Exception( $message );
        }
        else
        {
            $level = new Mage_Core_Exception( $message );
        }
        
        throw $level;
    }
    
    protected function isAdminPage()
    {
        return (Mage::app()->getRequest()->getRequestedRouteName() == self::ADMIN_PAGE);
    }
}
