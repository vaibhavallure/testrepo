<?php


class Allure_TeamworkDam_Model_Process extends Mage_Core_Model_Abstract{
    public const STATUS_PENDING="pending";
    public const STATUS_RUNNING="running";
    public const STATUS_COMPLETE="complete";
    public const STATUS_FAILED="failed";



    protected function _construct()
    {
        $this->_init('teamworkdam/process');
    }

    public function start($info="")
    {
       $data['process_info']=$info;
       $data['process_status']=self::STATUS_RUNNING;
       $data['started_at']=Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');

       $this->addData($data)->save();
       return $this;
    }

    public function end($info="",$status=self::STATUS_COMPLETE)
    {
         $this->setProcessStatus($status);
         $process_info=$this->getProcessInfo().'|END:'.$info;
         $this->setProcessInfo($process_info);
         $this->save();

         return $this;
    }
}