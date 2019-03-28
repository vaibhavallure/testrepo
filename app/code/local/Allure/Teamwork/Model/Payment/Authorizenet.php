<?php

class Allure_Teamwork_Model_Payment_Authorizenet extends Mage_Payment_Model_Method_Cc
{
    protected $_code  = "tm_pay_authrize";
    protected $_canSaveCc   = true;
}
