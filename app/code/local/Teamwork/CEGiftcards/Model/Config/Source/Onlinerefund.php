<?php

class Teamwork_CEGiftcards_Model_Config_Source_Onlinerefund
{
    const NEVER = 'never';
    const ONLINE_CREDITMEMO = 'online_creditmemo';
    const ANY_CREDITMEMO = 'any_creditmemo';

    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'never',
                'value' => self::NEVER
            ),
            array(
                'label' => 'when online creditmemo created',
                'value' => self::ONLINE_CREDITMEMO
            ),
            array(
                'label' => 'when online or offline creditmemo created',
                'value' => self::ANY_CREDITMEMO
            ),
        );
    }
}
