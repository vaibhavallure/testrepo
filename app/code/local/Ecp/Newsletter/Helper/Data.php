<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Ecp_Newsletter_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_NEWSLETTER_MAILS  = 'ecp_newsletter/newsletter_subscription_transactional_mails/enabled';

    public function getNewsletterSubscriptionMailEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_NEWSLETTER_MAILS);
    }
}