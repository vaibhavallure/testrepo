<?php

$installer = $this;

$installer->startSetup();

$profile = Mage::getModel('dataflow/profile');
$data = array(

    'name' => "Carga de Imagenes",
    'actions_xml' =>   '<action type="uploadimages/convert_adapter_io" method="generateArrayFile">
    <var name="destinyFolder"><![CDATA[var/import/imagesFile]]></var>
    <var name="outputFilename"><![CDATA[arrayImages.csv]]></var>
</action>

<action type="uploadimages/convert_adapter_io" method="load">
    <var name="type"><![CDATA[file]]></var>
    <var name="path"><![CDATA[var/import/imagesFile]]></var>
    <var name="filename"><![CDATA[arrayImages.csv]]></var>
    <var name="format"><![CDATA[txt]]></var>
</action>

<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[;]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames"></var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">uploadimages/convert_adapter_product</var>
    <var name="method">load</var>
</action>'
);
if (isset($data)) {
   $profile->addData($data);
}
try {
      $profile->save();
} catch (Exception $e){
      die($e->getMessage());
}

if (!is_dir(Mage::getBaseDir('var') . '/import/images')) {
    mkdir(Mage::getBaseDir('var') . '/import/images');
    chmod(Mage::getBaseDir('var') . '/import/images',0777);
}

if (!is_dir(Mage::getBaseDir('var') . '/import/imagesFile')) {
    mkdir(Mage::getBaseDir('var') . '/import/imagesFile');
    chmod(Mage::getBaseDir('var') . '/import/imagesFile',0777);
}

if (!is_dir(Mage::getBaseDir('var') . '/import/imagesCompleted')) {
    mkdir(Mage::getBaseDir('var') . '/import/imagesCompleted');
    chmod(Mage::getBaseDir('var') . '/import/imagesCompleted',0777);
}

$installer->endSetup(); 