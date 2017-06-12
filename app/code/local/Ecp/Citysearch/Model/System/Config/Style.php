<?php

class Ecp_Slides_Model_System_Config_Style {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $paths = array(
            Mage::getBaseDir() . DS . 'app/design/frontend/base/default/template/slides/style',
            //Mage::getBaseDir() . DS . 'app/design/frontend/default/default/template/slides/style'
        );

        $files = array();
        foreach ($paths as $path) {
            $tmp = $this->_getPHTMLFiles($path);
            if ($tmp)
                $files = array_merge($files, $tmp);
        }

        $styles = array();
        foreach ($files as $file) {
            //Mage::log(print_r($file,true));
            $styles[] = array(
            'value' =>$file['path'],
            'label' =>$file['basename']
            );
        }
        //Mage::log(print_r($styles,true));
        return $styles;
    }

    private function _getPHTMLFiles($path) {
        try {
            if (!file_exists($path))
                return false;
            $dir = new DirectoryIterator($path);
            $return = array();
            foreach ($dir as $file) {
                if($file->isFile()){
                    $tmp = pathinfo($file->getBasename());
                    if($tmp['extension']=='phtml'){
                        $tmp['path'] = $file->getPathname();
                        array_push($return,$tmp);
                    }

                }
                        
            }
        } catch (Exception $e) {
            return false;
        }
        return $return;
    }

}
