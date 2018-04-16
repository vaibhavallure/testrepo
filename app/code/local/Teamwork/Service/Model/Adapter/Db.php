<?php
class Teamwork_Service_Model_Adapter_Db
{
    protected $_db;
    function __construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function getTable($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    public function quoteInto($text, $value, $type = null, $count = null)
    {
        return $this->_db->quoteInto($text, $value, $type, $count);
    }

    public function arrayToSqlList($array)
    {
        return '"' . implode('","' , $array) . '"';
    }

    public function insert($table, $data)
    {
        $table = $this->getTable($table);
        $this->_db->insert($table, $data);
    }

    public function update($table, $data, $where)
    {
        $table = $this->getTable($table);
        if(!empty($where))
        {
            $query = array();
            foreach($where as $k => $v)
            {
                if (is_array($v)) {
                    foreach($v as $con => $val) {
                        $query[] = "$k $con $val";
                        break;
                    }
                    continue;
                }
                $query[] = "$k = '$v'";
            }
            $this->_db->update($table, $data, $query);
        }
    }

    public function delete($table, $where)
    {
        $table = $this->getTable($table);
        if(!empty($where))
        {
            $query = array();
            foreach($where as $k => $v)
            {
                $query[] = is_array($v) ? "$k IN ({$this->arrayToSqlList($v)})" : "$k = '$v'";
            }
            $this->_db->delete($table, $query);
        }
    }

    public function straightDelete($table, $where)
    {
        $table = $this->getTable($table);
        $this->_db->delete($table, $where);
    }  

    public function getOne($table, $where = array(), $field = '*')
    {
        $select = $this->_db->select()->from($this->getTable($table), $field);
        if(!empty($where))
        {
            foreach($where as $k => $v)
            {
                $select->where("$k = ?", $v);
            }
        }

        if($field == '*')
        {
            return $this->_db->fetchRow($select);
        }
        else
        {
            return $this->_db->fetchOne($select);
        }
    }

    public function getAll($table, $where = array(), $field = array('*'))
    {
        $select = $this->_db->select()->from($this->getTable($table), $field);
        if(!empty($where))
        {
            foreach($where as $k => $v)
            {
                $select->where("$k = ?", $v);
            }
        }
        return $this->_db->fetchAll($select);
    }

    public function getResults($query)
    {
        if(!empty($query))
        {
            return $this->_db->fetchAll($query);
        }
    }

    public function query($sql)
    {
        $this->_db->query($sql);
    }
}