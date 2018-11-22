<?php
class Allure_Appointments_Model_Adminhtml_Source_Timing
{
    public function toOptionArray()
    {
		$lower = 0;
		$upper = 24;

		$step = 0.5;

		foreach ( range( $lower, $upper, $step ) as $time ) {
			$array[] = array('value' => $time, 'label' => sprintf("%02d:%02d", $time % 60, ($time * 60) % 60 ));
		}

    	return $array;
    }
}
