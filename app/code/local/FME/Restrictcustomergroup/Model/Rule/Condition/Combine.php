<?php
/**
 * Restrict customer group extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Restrictcustomergroup
 * @author     R.Rao <rafay.tahir@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
 
 class FME_Restrictcustomergroup_Model_Rule_Condition_Combine
     extends Mage_CatalogRule_Model_Rule_Condition_Combine  {
          
      public function getConditions()
      { 
          if ($this->getData($this->getPrefix()) === null)
              $this->setData($this->getPrefix(), array());
          return $this->getData($this->getPrefix());
      }
   
      public function getNewChildSelectOptions()
      {
          $conditions = parent::getNewChildSelectOptions();
          foreach ($conditions as $index => $condition) {
              if (isset($condition['value']) && $condition['value'] == 'catalogrule/rule_condition_combine') {
                  $conditions[$index]['value'] = 'restrictcustomergroup/rule_condition_combine';
                  break;
              }
          }
          return $conditions;
      }

}
