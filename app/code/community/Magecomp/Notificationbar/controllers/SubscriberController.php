<?php
class Magecomp_Notificationbar_SubscriberController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		try
		{
			$email=Mage::app()->getRequest()->getParam('email');
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return json_encode("Invalid Email Format");
			}
			else
			{
				if(Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter'))
				{	
					$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
					if($subscriber->getSubscriberEmail()==$email && $email!=""){
						return json_encode('The Email is Already Subscribed.');
					}
					else{
							Mage::getModel('newsletter/subscriber')->subscribe($email);
							return json_encode('You have Successfully Subscribed.');
					}
				}
				else
				{
					return "Newsletter Event is Disabled in Your Store Backend.";
				}
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
			Mage::log($e->getMessage(),null,"notificationbar.log");
		}
	}
}