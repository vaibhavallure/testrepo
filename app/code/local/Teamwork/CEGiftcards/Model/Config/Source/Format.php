<?php

class Teamwork_CEGiftcards_Model_Config_Source_Format extends Mage_Core_Model_Abstract
{
    const CODE_FORMAT_ALPHANUM = 'alphanum';
    const CODE_FORMAT_ALPHA = 'alpha';
    const CODE_FORMAT_NUM = 'num';


    /**
     * Return list of gift card account code formats
     *
     * @return array
     */
    public function getOptions()
    {
        return array(
            self::CODE_FORMAT_ALPHANUM
                => Mage::helper('teamwork_cegiftcards')->__('Alphanumeric'),
            self::CODE_FORMAT_ALPHA
                => Mage::helper('teamwork_cegiftcards')->__('Alphabetical'),
            self::CODE_FORMAT_NUM
                => Mage::helper('teamwork_cegiftcards')->__('Numeric'),
        );
    }

    /**
     * Return list of gift card account code formats as options array.
     * If $addEmpty true - add empty option
     *
     * @param boolean $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = false)
    {
        $result = array();

        if ($addEmpty) {
            $result[] = array('value' => '',
                              'label' => '');
        }

        foreach ($this->getOptions() as $value=>$label) {
            $result[] = array('value' => $value,
                              'label' => $label);
        }

        return $result;
    }
}
