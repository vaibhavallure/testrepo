<?php
class Magecomp_Notificationbar_Model_Source_Goal{
	public function toOptionArray(){
		return array(
			array('value'=>'0','label'=>'Promote a Sale/Discount'),
			array('value'=>'1','label'=>'Talk to Your Visitors'),
			array('value'=>'2','label'=>'Social'),
			array('value'=>'3','label'=>'Grow your Mailing List'),
			array('value'=>'4','label'=>'Countdown Timer'),
		);	
	}
}