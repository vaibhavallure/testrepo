<?php
/**
 * Allure_InstaCatalog
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Model_Resolutions
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=>'thumbnail', 'label'=>'thumbnail (default) - 150x150'),
      array('value'=>'low_resolution', 'label'=>'low_resolution - 306x306'),
      array('value'=>'standard_resolution', 'label'=>'standard_resolution - 612x612'),      
    );
  }
}
