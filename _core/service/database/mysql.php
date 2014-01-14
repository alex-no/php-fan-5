<?php  namespace core\service\database;
/**
 *
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Otchenashenko Sergey (dinvisible@gmail.com)
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.005 (14.01.2014)
 */
class mysql extends base
{

    /**
     * @var link of Connection by 'mysql_pconnect' OR 'mysql_connect'
     */
    protected $lConnent = null;

    /**
     * @var string Parsed Sql query
     */
    protected $sParsedSql = '';

    // ======== Main Interface methods ======== \\
    /**
     * Restore closed database connection
     * @param array $aParam
     * @param boolean $bMakeException Make Exception if connection impossible
     * @return boolean
     */
    public function reconnect($aParam, $bMakeException = true)
    {
        $this->sParsedSql = '';

        $aParamConnect = array();
        foreach (array('HOST', 'USER', 'PASSWORD') as $k) {
            if (empty($aParam[$k])) {
                break;
            }
            $aParamConnect[] = $aParam[$k];
        }
        if (count($aParamConnect) == 3) {
            $aParamConnect[] = true; //Always create new link
        }
        $sFunc    = $aParam['PERSISTENT'] ? 'mysql_pconnect' : 'mysql_connect';
        $lConnent = @call_user_func_array($sFunc, $aParamConnect);
        if (empty($lConnent)) {
            $this->_fixError(
                    1,
                    'Connect to mysql server.',
                    mysql_errno(),
                    'Could not connect: ' . mysql_error(),
                    $bMakeException
            );
            return false;
        }
        if (@mysql_select_db($aParam['DATABASE'], $lConnent)) {
            $this->lConnent = $lConnent;
        } else {
            $this->_fixError(
                    2,
                    'Select mysql DB.',
                    mysql_errno($lConnent),
                    'Can\'t use DB "' . $aParam['DATABASE'] . '": ' . mysql_error($lConnent),
                    $bMakeException
            );
            return false;
        }
        return true;
    } // function reconnect

    /**
     * Close connection
     */
    public function connectionClose()
    {
        if (!empty($this->lConnent)) {
            mysql_close($this->lConnent);
            $this->lConnent = null;
        }
        return $this;
    } // function connectionClose

    /**
     * Execute SQL query
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param integer $iResultType
     * @return object Result set
     */
    public function execute($sSql, $aParam = null, $iResultType = null)
    {
        $this->sParsedSql = '';

        if (empty($this->lConnent)) {
            $this->_fixError(
                    3,
                    'Prepare to execute SQL.',
                    0,
                    'Connect to MySQL isn\'t set.',
                    true
            );
            return null;
        }

        $mResult = mysql_query($this->_parseSql($sSql, $aParam, true), $this->lConnent);
        if (empty($mResult)) {
            $this->_fixError(
                    4,
                    'Execute SQL.',
                    mysql_errno($this->lConnent),
                    'Invalid query: ' . mysql_error($this->lConnent),
                    false
            );
            return null;
        }

        if (!is_bool($mResult)) {
            if (is_null($iResultType) || !$this->_isValidType($iResultType)) {
                $iResultType = $this->iResultType;
            }
            $aResult = array();
            do {
                $aLine = mysql_fetch_array($mResult, $iResultType);
                if ($aLine) {
                    $aResult[] = $aLine;
                }
            } while(!empty($aLine));
            mysql_free_result($mResult);
            return $aResult;
        }
        return $mResult;
    } // function execute

    /**
     * Start Transaction
     * @return mixed
     */
    public function startTransaction()
    {
        return $this->execute('START TRANSACTION');
    } // function startTransaction

    /**
     * Set SavePoint
     * @param string $sSavePoint
     * @return mixed
     */
    public function setSavePoint($sSavePoint)
    {
        return $this->execute('SAVEPOINT ?', $sSavePoint);
    } // function setSavePoint

    /**
     * Commit Transaction
     * @return mixed
     */
    public function commit()
    {
        return $this->execute('COMMIT');
    } // function commit

    /**
     * Rollback Transaction
     * @param string $sSavePoint
     * @return mixed
     */
    public function rollback($sSavePoint = null)
    {
        return empty($sSavePoint) ? $this->execute('ROLLBACK') : $this->execute('ROLLBACK TO SAVEPOINT ?', $sSavePoint);
    } // function rollback

    /**
     * Get last insert id
     * @return int Id
     */
    public function getInsertId()
    {
        if (empty($this->lConnent)) {
            return null;
        }
        $aResult = $this->execute('SELECT LAST_INSERT_ID() AS id', null, MYSQL_ASSOC);
        return $aResult[0]['id'];
    } // function getInsertId

    /**
     * Get one value
     * @param string $sSql SQL query
     * @param string $sFieldName Field Name
     * @param array $aParam Input parameters
     * @return mixed
     */
    public function getOne($sSql, $sFieldName, $aParam = null)
    {
        $aResult = $this->execute($sSql, $aParam, MYSQL_ASSOC);
        return empty($aResult) ? null : $aResult[0][$sFieldName];
    } // function getOne

    /**
     * Get row
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @param integer $iResultType
     * @return object Result set
     */
    public function getRow($sSql, $aParam = null, $iResultType = null)
    {
        $aResult = $this->execute($sSql, $aParam, $iResultType);
        return empty($aResult) ? array() : $aResult[0];
    } // function getRow

    /**
     * Get row assoc
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return object Result set
     */
    public function getRowAssoc($sSql, $aParam = null)
    {
        return $this->getRow($sSql, $aParam, MYSQL_ASSOC);
    } // function getRowAssoc

    /**
     * Get col
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return object Result set
     */
    public function getCol($sSql, $sColName, $aParam = null)
    {
        $aResult = array();
        $aTmp = $this->execute($sSql, $aParam, is_string($sColName) ? MYSQL_ASSOC : MYSQL_NUM);
        if (!empty($aTmp)) {
            foreach ($aTmp as $v) {
                $aResult[] = isset($v[$sColName]) ? $v[$sColName] : null;
            }
        }
        return $aResult;
    } // function getCol

    /**
     * Get assoc
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return object Result set
     */
    public function getAssoc($sSql, $aParam = null)
    {
        $aResult = array();
        $aTmp = $this->execute($sSql, $aParam, MYSQL_ASSOC);
        if (!empty($aTmp)) {
            foreach ($aTmp as $v) {
                $k = array_shift($v);
                $aResult[$k] = $v;
            }
            return $aResult;
        }
        return array();
    } // function getAssoc

    /**
     * Get all
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return object Result set
     */
    public function getAll($sSql, $aParam = null, $iResultType = null)
    {
        $aResult = $this->execute($sSql, $aParam, $iResultType);
        return empty($aResult) ? array() : $aResult;
    } // function getAll

    /**
     * Get all
     * @param string $sSql SQL query
     * @param array $aParam Input parameters
     * @return object Result set
     */
    public function getAllLimit($sSql, $aParam = null, $nQtt = -1, $nOffset = -1, $iResultType = null, $iResultType = null)
    {
        if ($nQtt > -1) {
            $sSql .= ' LIMIT ';
            $sSql .= $nOffset > -1 ? $nOffset . ', ' . $nQtt  : $nQtt;
        }
        return $this->getAll($sSql, $aParam, $iResultType);
    } // function getAllLimit

    /**
     * Get MySQL version
     * @return string
     */
    public function getVersion()
    {
        if (empty($this->lConnent)) {
            return null;
        }
        $aResult = $this->execute('SELECT VERSION() AS ver', null, MYSQL_ASSOC);
        return 'MySQL ' . $aResult[0]['ver'];
    } // function getVersion

    /**
     * Get Status of table
     * Result array has next fields:
     *   Name, Engine, Version, Row_format, Rows, Avg_row_length, Data_length,
     *   Max_data_length, Index_length, Index_length, Data_free, Auto_increment,
     *   Create_time, Update_time,Check_time, Collation, Checksum, Create_options, Comment
     * @param string $sTableName Name of Table
     * @return array
     */
    public function getTableStatus($sTableName)
    {
        $aResult = $this->execute('SHOW TABLE STATUS LIKE ?', array($sTableName));
        return $aResult[0];
    } // function getTableStatus

    /**
     * Modifiy SQL-Query - replace Placeholders by parameters
     * @param string $sSql
     * @param array $aParam
     * @return string
     */
    public function parseSql($sSql, $aParam)
    {
        return $this->_parseSql($sSql, $aParam, false);
    } // function parseSql

    /**
     * Return last parsed and executed SQL
     * @return string
     */
    public function getParsedSql()
    {
        return $this->sParsedSql;
    } // function getParsedSql

    // ======== Private/Protected methods ======== \\
    /**
     * Modifiy SQL-Query - replace Placeholders by parameters
     * @param string $sSql
     * @param array $aParam
     * @param boolean $bSaveResult
     * @return string
     */
    protected function _parseSql($sSql, $aParam, $bSaveResult)
    {
        if (!empty($aParam)) {

            if (!is_array($aParam)) {
                $aParam = array($aParam);
            }

            $aSqlArr = explode('?', $sSql);
            $sSql = '';
            foreach ($aParam as $v) {
                if (empty($aSqlArr)) {
                    // ToDo: Maybe made exception if $aSqlArr is empty.
                    trigger_error('Quantity of parameters more than quantity of placeholders.', E_USER_WARNING);
                    $sSql .= ' ';
                } else {
                    $sSql .= array_shift($aSqlArr);
                }
                if (is_null($v)) {
                    $sSql .= 'NULL';
                } else {
                    switch (gettype($v)) {
                    case 'integer' :
                        $sSql .= $v;
                        break;
                    case 'double' :
                        $sSql .= str_replace(',', '.', $v);
                        break;
                    case 'boolean' :
                        $sSql .= $v ? 1 : 0;
                        break;
                    case 'object' :
                        $v = method_exists($v, '__toString') ? $v->__toString() : (string)$v;
                    default:
                        if (is_scalar($v)) {
                            $sSql .= '\'' . mysql_real_escape_string($v, $this->lConnent) . '\'';
                        } else {
                            // ToDo: Maybe made exception there if count of elements in $aSqlArr more than 1;
                            trigger_error('Incorrect type of placeholder "' . gettype($v) . '".', E_USER_WARNING);
                        }
                    }
                }
            }
            if (!empty($aSqlArr)) {
                if (count($aSqlArr) > 1) {
                    // ToDo: Maybe made exception there if count of elements in $aSqlArr more than 1;
                    trigger_error('Quantity of parameters less than quantity of placeholders.', E_USER_WARNING);
                }
                $sSql .= implode('?', $aSqlArr);
            }
        }
        if ($bSaveResult) {
            $this->sParsedSql = $sSql;
        }
        return $sSql;
    } // function _parseSql

} // class \core\service\database\mysql
?>