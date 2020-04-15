<?php
class Magecomp_Notificationbar_Model_Source_Effect{
	public function toOptionArray(){
		return array(
			array('value'=>'none','label'=>'None'),
			array('value'=>'bounce','label'=>'Bounce'),
			array('value'=>'pulse','label'=>'Pulse'),
			array('value'=>'rubberBand','label'=>'RubberBand'),
			array('value'=>'shake','label'=>'Shake'),
			array('value'=>'swing','label'=>'Swing')
		);	
	}
}