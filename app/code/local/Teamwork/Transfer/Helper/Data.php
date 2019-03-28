<?php
/**
 * Data helper
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Safe php float subtract operation
     *
     * @param float $left_operand
     * @param float $right_operand
     *
     * @return string|float
     */
    public function floatSubtraction($left_operand, $right_operand)
    {
        /*use safe subtraction if php bcsub function is available in current php realization*/
        if(function_exists('bcsub'))
        {
            $result = bcsub(floatval($left_operand), floatval($right_operand), 6);
        }
        /*will be better to make round due possible php subtract problem in other case*/
        else
        {
            $result = round(floatval($left_operand) - floatval($right_operand), 6);
        }
        return $result;
    }
    
    /**
     * Checks whether ECM image can be added to item
     *
     * @param array $item item to check
     * @param array $image image to check
     *
     * @return bool
     */
    public function canImageBeAddedToItem($item, $image)
    {
        $array_empty     = array('');
        $itemAttributes  = array_diff(array($item['attribute1_id'], $item['attribute2_id'], $item['attribute3_id']), $array_empty);
        $imageAttributes = array_diff(array($image['attribute1'],   $image['attribute2'],   $image['attribute3']),   $array_empty);

        $matchingAttributes = array_intersect($itemAttributes, $imageAttributes);

        // if item have all attributes image is assigned to, return true, otherwise false
        return ((count($imageAttributes) > 0) && (count($matchingAttributes) == count($imageAttributes)));
    }
    
    public function generateGuid($namespace='')
    {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : '';
        $data .= !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data .= !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '';
        $hash = strtolower(hash('ripemd128', $uid . $guid . md5($data)));

        return Mage::helper('teamwork_service')->getGuidFromString($hash, true);
    }

    /**
     * Extension for array_diff function.
     * Subtracts subtrahendArray from minuendArray.
     * 
     * Minuend and subtrahend arrays are 2-level assoc arrays like (<key1> => <subArray1>, <key2> => <subArray2> ...)
     * Subtract condition: subArray[$keyField] (minuend) = subArray[$keyField] (subtrahend)
     *
     * @param  array   $minuendArray
     * @param  array   $subtrahendArray
     * @param  string  $keyField
     * @param  boolean $leaveOriginalArrayKeys if true, result will contain original keys from minuendArray
     *
     * @return array
     */
    public function multiArrayDiffByField($minuendArray, $subtrahendArray, $keyField, $leaveOriginalArrayKeys = false)
    {
        $subtrahendKeyFieldValues = array();
        foreach ($subtrahendArray as $values)
        {
            $subtrahendKeyFieldValues[] = $values[$keyField];
        }

        $result = array();
        foreach ($minuendArray as $key => $values) 
        {
            if (!in_array($values[$keyField], $subtrahendKeyFieldValues))
            {
                if ($leaveOriginalArrayKeys) 
                {
                    $result[$key] = $values;
                }
                else 
                {
                    $result[] = $values;
                }
            }
        }
        return $result;
    }

    /**
     * Array_column realization for PHP lower than 5.5. Was taken from https://github.com/ramsey/array_column/blob/master/src/array_column.php
     *
     * @param  array $input
     * @param  string $columnKey
     * @param  string $indexKey
     *
     * @return array
     */
    public function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }
	
	public function checkModuleAvailability($moduleName, $className)
    {
		if( Mage::helper('core')->isModuleEnabled($moduleName) )
		{
			try
			{
				if( class_exists($className) )
				{
					return true;
				}
			}
			catch(Exception $e){
				return false;
			}
		}
		return false;
    }
    
    public function getProductFactory()
    {
        $productTypes = array();
		$productEmulator = new Varien_Object();
        foreach (array_keys(Mage_Catalog_Model_Product_Type::getTypes()) as $typeId) {
            $productEmulator->setTypeId($typeId);
            $productTypes[$typeId] = Mage::getSingleton('catalog/product_type')
                ->factory($productEmulator);
        }
        return $productTypes;
    }
}