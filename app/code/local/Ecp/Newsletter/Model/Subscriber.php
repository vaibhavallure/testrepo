<?php

class Ecp_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    /**
     * Subscribes by email
     *
     * @param string $email
     * @throws Exception
     * @return int
     */
    public function subscribeExtended($email,$firstname,$lastname,$country)
    {
        
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed   = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true){
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
            $this->setFirstName($firstname);
            $this->setLastName($lastname);
            $this->setCountry($country);
        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ((bool) Mage::helper('ecp_newsletter')->getNewsletterSubscriptionMailEnabled()) {
                if ($isConfirmNeed === true
                    && $isOwnSubscribes === false
                ) {
                    $this->sendConfirmationRequestEmail();
                } else {
                    $this->sendConfirmationSuccessEmail();
                }
            }
            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Sends out confirmation success email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendConfirmationSuccessEmail()
    {
        if ((bool) Mage::helper('ecp_newsletter')->getNewsletterSubscriptionMailEnabled())
        {
            return parent::sendConfirmationSuccessEmail();
        }
        else
            return $this;
    }

    /**
     * Sends out unsubsciption email
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function sendUnsubscriptionEmail()
    {
        if ((bool) Mage::helper('ecp_newsletter')->getNewsletterSubscriptionMailEnabled())
        {
            return parent::sendUnsubscriptionEmail();
        }
        else
            return $this;
    }
}