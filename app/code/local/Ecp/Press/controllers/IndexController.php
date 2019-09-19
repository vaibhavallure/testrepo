<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Press
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Press
 *
 * @category    Ecp
 * @package     Ecp_Press
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Press_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
    public function getPressAction()
    {
        $page=$this->getRequest()->getParam("page");
        $collection = Mage::getModel('ecp_press/press')->getCollection()
            ->addFieldToSelect('image_one')
            ->addFieldToSelect('press_id')
            ->addFilter('status', 1)
            ->setOrder('publish_date');
        $collection->setCurPage($page);
        $collection->setPageSize(9);

        if($collection->getLastPageNumber()>=$page) {
            echo json_encode($collection->getData());
        }
    }
    public function getPopupAction()
    {
        $press_id=$this->getRequest()->getParam("id");
        $press=Mage::getModel('ecp_press/press')->load($press_id);

        $data['title']=$press->getTitle();
        $data['publish_date']=date("F d, Y",strtotime($press->getPublishDate()));

        $dataArr=array_filter($press->getData());
        unset($dataArr['image_one']);
        $images = array_filter($dataArr,function ($key){ return(strpos($key,'image_') !== false);}, ARRAY_FILTER_USE_KEY);
        $data['img']=array_values($images);
        echo json_encode($data);
    }
}