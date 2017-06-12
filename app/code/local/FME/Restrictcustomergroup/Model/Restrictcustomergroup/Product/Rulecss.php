<?php
/**
 * Background Images extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Backgroundimages
 * @author     R.Rao <rafay.tahir@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
class FME_Restrictcustomergroup_Model_Restrictcustomergroup_Product_Rulecss
   extends FME_Restrictcustomergroup_Model_Restrictcustomergroup_Rule
{
   public function getConditionsInstance()
   { 
        return Mage::getModel('restrictcustomergroup/rule_condition_combine');
   }

   public function _resetConditions($conditions=null)
   { 
        parent::_resetConditions($conditions);
        $this->getConditions($conditions)
                ->setId('css_conditions')
                ->setPrefix('css');
                
        return $this;
   }

}
