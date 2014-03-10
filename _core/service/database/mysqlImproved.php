<?php namespace fan\core\service\database;
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class mysqlImproved extends base
{
    // ======== Main Interface methods ======== \\
    /**
     * Restore closed database connection
     * @param array $aParam
     * @param boolean $bMakeException Make Exception if connection impossible
     * @return type
     */
    public function reconnect($aParam, $bMakeException = true)
    {
    } // function reconnect

    /**
     * Close connection
     */
    public function connectionClose()
    {
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
        $aTmp = $this->execute($sSql, $aParam, MYSQL_ASSOC);
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

} // class \fan\core\service\database\mysqlImproved
?>