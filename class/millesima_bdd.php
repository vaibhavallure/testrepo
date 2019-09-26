<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 26/02/15
 * Time: 11:38
 * To change this template use File | Settings | File Templates.
 */

class Millesima_Bdd
{
    const HOST = 'millesima-emailing-mysql';
    const USERNAME = 'emailing';
    const PASSWORD = 'emailing';
    const DBNAME = 'emailing';

    /**
     *
     * variable qui stock la connection à la base
     * @var unknown_type
     */
    private $_dbInstance = '';

    public function getDbInstance()
    {
        if($this->_dbInstance == ''){
            try {
                $dbInstance = new PDO('mysql:host='.self::HOST.';dbname='.self::DBNAME, self::USERNAME, self::PASSWORD);
                $dbInstance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
                $this->_dbInstance = $dbInstance;
                
            }
            catch (Exception $e) {
                throw $e;
            }
        }
        return $this->_dbInstance;
    }

    /**
     * Perform a delete statement, sql should be "DELETE"
     * @param string $sql : DELETE statement sql (placeholders allowed)
     * @param array $params : placeholder replacements (can be null)
     */
    public function delete($sql,$params=null)
    {
        $this->exec_stmt($sql,$params);
    }

    /**
     * Performs an update statement
     * Enter description here ...
     * @param string $sql UPDATE statement sql (placeholder allowed)
     * @param array $params parameter values if placeholders in SQL
     */
    public function update($sql,$params=null)
    {
        $this->exec_stmt($sql,$params);
    }
    /**
     * Perform an insert , sql should be "INSERT"
     * @param string $sql :INSERT statement SQL (placeholders allowed)
     * @param array $params : placeholder replacements (can be null)
     * @return mixed : last inserted id
     */
    public function insert($sql,$params=null)
    {

        $this->exec_stmt($sql,$params);
        $liid=$this->getDbInstance()->lastInsertId();
        return $liid;
    }

    /**
     * Perform a select ,sql should be "SELECT"
     * @param string $sql :SELECT statement SQL (placeholders allowed)
     * @param array $params : placeholder replacements (can be null)
     * @return PDOStatement : statement instance for further processing
     */
    public function select($sql,$params=null)
    {
        return $this->exec_stmt($sql,$params,false);
    }

    /**
     * Perform a truncate ,sql should be "truncate"
     * @param string $sql :truncate statement SQL (placeholders allowed)
     * @param array $params : placeholder replacements (can be null)
     * @return PDOStatement : statement instance for further processing
     */
    public function truncate($sql,$params=null)
    {
        return $this->exec_stmt($sql,$params,false);
    }

    /**
     * Selects one unique value from one single row
     * @param $sql : SELECT statement SQL (placeholders allowed)
     * @param $params :placeholder replacements (can be null)
     * @param $col : column value to retrieve
     * @return mixed : null if not result , wanted column value if match
     */
    public function selectone($sql,$params,$col)
    {
        $stmt=$this->select($sql,$params);

        $r=$stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $v=(is_array($r)?$r[$col]:null);
        unset($r);
        return $v;
    }

    /**
     * Selects all values from a statement into a php array
     *
     * @param  $sql : sql select to execute
     * @param  $params : placeholder replacements (can be null)
     * @return mixed
     */
    public function selectAll($sql,$params=null)
    {
        $stmt=$this->select($sql,$params);

        $r=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $r;
    }

    /**
     * test if value exists (test should be compatible with unique select)
     * @param $sql : SELECT statement SQL (placeholders allowed)
     * @param $params :placeholder replacements (can be null)
     * @param $col : column value to retrieve
     * @return boolean : true if value found, false otherwise
     */
    public function testexists($sql,$params,$col)
    {
        return $this->selectone($sql,$params,$col)!=null;
    }

    /**
     *
     * Checks wether an array is associative
     * @param mixed $var array or variable to test
     * @return mixed
     */
    public function is_assoc($var) {
        return is_array($var) && array_keys($var)!==range(0,sizeof($var)-1);
    }

    /**
     * executes an sql statement
     * @param string $sql : sql statement (may include ? placeholders or named variables)
     * @param array $params : parameters to replace placeholders (can be null)
     * @param boolean $close : auto close cursor after statement execution (defaults to true)
     * @return PDOStatement : statement for further processing if needed
     */
    public function exec_stmt($sql,$params=null,$close=true)
    {
        //create new prepared statement
        $stmt=$this->getDbInstance()->prepare($sql);

        if ($params!=null) {
            if (!$this->is_assoc($params)) {
                $params=is_array($params)?$params:array($params);
                $stmt->execute($params);
            } else {
                foreach( $params as $pname=>$pval) {
                    if (count(explode(":",$pname)) == 1) {
                        $val=strval($pval);
                        $stmt->bindValue(":$pname",$val);
                    }
                }
                $stmt->execute();
            }
        } else {
            $stmt->execute();
        }
        if ($close) {
            $stmt->closeCursor();
        }

        unset($params);
        return $stmt;
    }

}