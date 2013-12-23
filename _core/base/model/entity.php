<?php namespace core\base\model;
use project\exception\model\entity\fatal as fatalException;
/**
 * Entity - table data
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
 * @property-read \core\service\entity\description $description
 * @property-read \core\base\model\request $request
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.003 (23.12.2013)
 */
abstract class entity
{
    /**
     * Entity Name (suffix of NS with class-name)
     *
     * @var string
     */
    protected $sName = null;
    /**
     * Table Name
     * @var string
     */
    protected $sTableName = null;

    /**
     * Service of entity
     * @var \core\service\config\row
     */
    protected $oConfig = null;
    /**
     * Service of entity
     * @var \core\service\entity
     */
    protected $oService = null;
    /**
     * Service of entity
     * @var \core\service\database
     */
    protected $oConnection = null;
    /**
     * Connection Name for \core\service\database
     * @var string
     */
    protected $sConnectionName = null;
    /**
     * Connection Key for \core\service\database
     * @var string
     */
    protected $nConnectionKey = null;

    /**
     * @var array SQL-requests
     */
    protected $aSQL = array();

    /**
     * Description of table of current Entity
     * @var \core\service\entity\description
     */
    protected $oDescription = null;
    /**
     * Loader of SQL-request
     * @var \core\base\model\request
     */
    protected $oRequest = null;

    /**
     * Backup of Call-Parameters
     * @var array
     */
    protected $aBakParam = array();

    /**
     * Name of Class for Row-object
     * @var string
     */
    protected $sRowClassName = null;
    /**
     * Name of Class for Rowset-object
     * @var string
     */
    protected $sRowsetClassName = null;
    /**
     * Name of Class for Request-object
     * @var string
     */
    protected $sRequestClassName = null;

    /**
     * Constructor of Table entity
     * Param keys:
     *   'connectionName', 'connectionKey', 'cacheEnabled',
     *   'tableName', 'primeryKey', 'fields', 'keys', 'relations',
     *   ''
     * @param \core\service\entity $oService
     * @param type $aParam
     */
    public function __construct(\core\service\entity $oService, $sName, $aParam = array())
    {
        $this->oService  = $oService;
        $this->sName     = $sName;

        $this->aBakParam = $aParam;
        $this->oConfig   = \project\service\config::instance('entity')->getEntityConfig($this, $sName);

        $this->_setConnectionParam($aParam);

        $this->_init($aParam);

        if (empty($this->sTableName)) {
            $this->sTableName = $this->_defineTableName($aParam);
        }

    } // function __construct

    // ======== The magic methods ======== \\

    /**
     * Magic method __set
     * @param string $sKey
     * @param mied $mValue
     * @throws fatalException
     */
    public function __set($sKey, $mValue)
    {
        throw new fatalException($this, 'There is impossible to set property "' . $sKey . '".');
    }

    /**
     * Magic method __get
     * @param string $sKey
     * @throws fatalException
     */
    public function __get($sKey)
    {
        $aProp = $this->_getPropertyList();
        if (!isset($aProp[$sKey])) {
            throw new fatalException($this, 'There is impossible to get property "' . $sKey . '".');
        }
        return $this->{$aProp[$sKey]}();
    }

    // ======== Main Interface methods ======== \\
    // --===-- Get Row --===-- \\
    /**
     * Get Row By Id (array, scalar value OR object with convert to string)
     * @param mixed $mRowId
     * @param boolean $bIdIsEncrypt
     * @return \core\base\model\row
     */
    public function getRowById($mRowId, $bIdIsEncrypt = false)
    {
        if (is_null($mRowId)) {
            $sClass = $this->getRowClassName();
            return new $sClass($this);
        }
        $mParam = $this->getParamById($mRowId, $bIdIsEncrypt);
        return $this->getRowByParam($mParam, 0, null);
    } // function getRowById

    /**
     * Get Row By Parameters
     * @param mixed $mParam
     * @param number $nOffset
     * @param string $sOrderBy
     * @return \core\base\model\row
     */
    public function getRowByParam($mParam = null, $nOffset = 0, $sOrderBy = null)
    {
        $sClass = $this->getRowClassName();
        $aData =& $this->getDataByParam($mParam, 1, $nOffset, $sOrderBy, true);
        return new $sClass($this, $aData);
    } // function getRowByParam

    /**
     * Get Row By Parameters or Create new One
     * @param array $aLoadParam
     * @param array $amSaveParam
     * @param boolean $bSaveNew
     * @return \core\base\model\row
     */
    public function getRowOrCreate($aLoadParam = null, $aSaveParam = array(), $bSaveNew = true)
    {
        $oRow = $this->getRowByParam($aLoadParam);
        if (!$oRow->checkIsLoad()) {
            $oRow->setFields(array_merge($aLoadParam, $aSaveParam), $bSaveNew);
        }
        return $oRow;
    } // function getRowOrCreate

    /**
     * Search record and return Row
     * @param string $sQueryKey key of SQL-request
     * @param mixed $mParam Assotiative array of field names and values
     * @param number $nOffset line
     * @param string $sOrderBy Select order
     * @return \core\base\model\row
     */
    public function getRowByKey($sQueryKey, $mParam = null, $nOffset = 0, $sOrderBy = null)
    {
        $oDesigner = $this->getSnippetyDesigner($sQueryKey)->setOrderPart($sOrderBy);
        return $this->getRowByQuery($oDesigner, $mParam, $nOffset);
    } // function getRowByKey

    /**
     * Get Row By the SQL-request (string OR \core\service\entity\designer) and Parameters
     * @param string|\core\service\entity\designer $mQuery
     * @param mixed $mParam Assotiative array of field names and values
     * @param number $nOffset line
     * @return \core\base\model\row
     */
    public function getRowByQuery($mQuery, $mParam = null, $nOffset = 0)
    {
        $sClass =  $this->getRowClassName();
        $aData  =& $this->getDataByQuery($mQuery, $mParam, 1, $nOffset, true);
        return new $sClass($this, $aData);
    } // function getRowByQuery

    // --===-- Get Rowset --===-- \\
    /**
     * Search records and return array of entities
     * @param mixed $mParam Assotiative array of field names and values
     * @param number $nQtt Quantity of rows (-1 - no limit)
     * @param number $nOffset line
     * @param string $sOrderBy Select order
     * @return \core\base\model\rowset
     */
    public function getRowsetByParam($mParam = null, $nQtt = -1, $nOffset = -1, $sOrderBy = '')
    {
        $sClass =  $this->getRowsetClassName();
        $aData  =& $this->getDataByParam($mParam, $nQtt, $nOffset, $sOrderBy);
        return new $sClass($this, $aData);
    } // function getRowsetByParam

    /**
     * Search records and return array of entities
     * @param string $sQueryKey key of SQL-request
     * @param mixed $mParam Assotiative array of field names and values
     * @param number $nQtt Quantity of rows (-1 - no limit)
     * @param number $nOffset line
     * @param string $sOrderBy Select order
     * @return \core\base\model\rowset
     */
    public function getRowsetByKey($sQueryKey, $mParam = null, $nQtt = -1, $nOffset = -1, $sOrderBy = '')
    {
        $oDesigner = $this->getSnippetyDesigner($sQueryKey)->setOrderPart($sOrderBy);
        return $this->getRowsetByQuery($oDesigner, $mParam, $nQtt, $nOffset);
    } // function getRowsetByKey

    /**
     * Get Rowset By the SQL-request (string OR \core\service\entity\designer) and Parameters
     * @param string|\core\service\entity\designer $mQuery
     * @param mixed $mParam Assotiative array of field names and values
     * @param number $nQtt Quantity of rows (-1 - no limit)
     * @param number $nOffset line
     * @return \core\base\model\rowset
     */
    public function getRowsetByQuery($mQuery, $mParam = null, $nQtt = -1, $nOffset = -1)
    {
        $sClass =  $this->getRowsetClassName();
        $aData  =& $this->getDataByQuery($mQuery, $mParam, $nQtt, $nOffset);
        return new $sClass($this, $aData);
    } // function getRowsetByQuery

    // --===-- Get Count --===-- \\
    /**
     * Get count records by parameters
     * @param mixed $mParam Assotiative array of field names and values
     * @return integer
     */
    public function getCountByParam($mParam = null)
    {
        $oQuery = $this->getDesigner('select')->setSelectByParam($mParam);
        return $this->getCountByQuery($oQuery, $mParam);
    } // function getCountByParam

    /**
     * Get count records by SQL-key and parameters
     * @param string $sQueryKey key of SQL-request
     * @param mixed $mParam Assotiative array of field names and values
     * @return integer
     */
    public function getCountByKey($sQueryKey, $mParam = null)
    {
        $oQuery = $this->getSnippetyDesigner($sQueryKey);
        return $this->getCountByQuery($oQuery, $mParam);
    } // function getCountByKey

    /**
     * Get count records by parameters
     * @param string|\core\service\entity\designer $mQuery
     * @param mixed $mParam Assotiative array of field names and values
     * @return integer
     */
    public function getCountByQuery($mQuery, $mParam = null)
    {
        list($sQuery, $aNewParam) = $this->_getSqlAsString($mQuery, $mParam);
        // ToDo: Take account of Union
        $aMatches = array();
        if (preg_match_all('/\s+ORDER\s+BY\s+.*/', $sQuery, $aMatches)) {
            $sQuery = str_replace(end($aMatches[0]), '', $sQuery);
        }

        $sMethod = $this->oConfig['COUNT_METHOD'];
        if (empty($sMethod)) {
            /* @var $oGlobalConf \core\service\config\row */
            $oGlobalConf = \project\service\config::instance('entity')->get('common');
            $sMethod = $oGlobalConf->get('DEFAULT_COUNT_METHOD', 'SUBQUERY');
        }

        $oServDb = $this->getConnection();
        switch (strtoupper($sMethod)) {
        case 'CALC_FOUND_ROWS':
            $sQueryTmp = preg_replace('/(?<=^|\W)SELECT\s/i', 'SELECT SQL_CALC_FOUND_ROWS ', $sQuery, 1);
            $oServDb->getAllLimit($sQueryTmp, $aNewParam, 1);
            $sQuery = 'SELECT FOUND_ROWS() as cnt';
            return $oServDb->getOne($sQuery, 'cnt');
        }
        $sQuery = 'SELECT count(*) as cnt FROM (' . $sQuery . ') as src';
        return $oServDb->getOne($sQuery, 'cnt', $aNewParam);
    } // function getCountByQuery

    /**
     * Get Table Name
     * @return string
     */
    public function getTableName()
    {
        return $this->sTableName;
    } // function getTableName
    // ---- Additional interface methods ---- \\
    /**
     * Get Array of parameters By Id (Id as array, scalar value OR object with converting to string)
     * @param mixed $mRowId
     * @param boolean $bIdIsEncrypt
     * @return \core\base\model\row
     * @throws fatalException
     */
    public function getParamById($mRowId, $bIdIsEncrypt = false)
    {
        $mIdName = $this->description->getPrimeryKey();
        if (is_scalar($mIdName)) {
            if (is_scalar($mRowId)) {
                $mParam[$mIdName] = $bIdIsEncrypt ? $this->getService()->getEncapsulant()->decryptId($mRowId) : $mRowId;
            } elseif (is_object($mRowId) && method_exists($mRowId, '__toString')) {
                $mParam[$mIdName] = $mRowId->__toString();
            } else {
                throw new fatalException($this, 'Value of ID for select data from "' . $this->getTableName() . '" must have scalar value.');
            }
        } elseif (is_array($mRowId) && count($mIdName) == count($mRowId)) {
            sort($mIdName);
            ksort($mRowId);
            if (array_diff($mIdName, array_keys($mRowId))) {
                foreach (array_values($mRowId) as $k => $v) {
                    $mParam[$mIdName[$k]] = $mRowId;
                }
            } else {
                $mParam = $mRowId;
            }
        } else {
            throw new fatalException($this, 'Value of ID for select data from "' . $this->getTableName() . '" must be as array.');
        }
        return $mParam;
    } // function getParamById

    /**
     * Get link to DATA (result of SQL-request) as Array by the Parameters
     * @param mixed $mParam
     * @param numeric $nQtt
     * @param numeric $nOffset
     * @param string $sOrderBy
     * @param boolean $bOnlyOne
     * @return array
     */
    public function &getDataByParam($mParam = null, $nQtt = -1, $nOffset = -1, $sOrderBy = null, $bOnlyOne = false)
    {
        $oQuery =  $this->getDesigner('select')->setSelectByParam($mParam, $sOrderBy);
        $aData  =& $this->getDataByQuery($oQuery, $mParam, $nQtt, $nOffset, $bOnlyOne);
        return $aData;
    } // function getDataByParam

    /**
     * Get link to DATA as Array by the SQL-request and Parameters
     * @param string|\core\service\entity\designer $mQuery
     * @param mixed $mParam
     * @param numeric $nQtt
     * @param numeric $nOffset
     * @param boolean $bOnlyOne
     * @return array
     * @throws fatalException
     */
    public function &getDataByQuery($mQuery, $mParam = null, $nQtt = -1, $nOffset = -1, $bOnlyOne = false)
    {
        list($sQuery, $aNewParam) = $this->_getSqlAsString($mQuery, $mParam, false);
        $aData = $this->getConnection()->getAllLimit($sQuery, $aNewParam, $nQtt, $nOffset);
        // ToDo: link Result to array as the property of this object
        if(!empty($aData) && $bOnlyOne) {
            $aData =& $aData[0];
        }
        return $aData;
    } // function getDataByQuery

    /**
     * Set the SQL-query by key
     * @param string $sQueryKey SQL-key
     * @param string $sValue
     * @return \core\base\model\entity
     */
    public function setSQL($sQueryKey, $sValue)
    {
        $this->getRequestLoader()->set($sQueryKey, $sValue);
        return $this;
    } // function setSQL
    /**
     * Get the SQL-query by key
     * @param string $sQueryKey SQL-key
     * @return string
     */
    public function getSQL($sQueryKey)
    {
        return $this->getRequestLoader()->get($sQueryKey);
    } // function getSQL
    /**
     * Get Snippety SQL-designer
     * @param string $sQueryKey SQL-key
     * @return \core\service\entity\designer\snippety
     */
    public function getSnippetyDesigner($sQueryKey)
    {
        $oDesigner = $this->getDesigner('snippety');
        /* @var $oDesigner \core\service\entity\designer\snippety */
        $oDesigner->setSqlRequest($sQueryKey);
        return $oDesigner;
    } // function getSnippetyDesigner

    /**
     * Set Connection of entity
     * @param type $mConnection
     * @param type $nExtraKey
     * @return \core\base\model\entity
     */
    public function setConnection($mConnection = null, $nExtraKey = 0)
    {
        if (empty($mConnection)) {
            $mConnection = $this->sConnectionName;
        }

        if (is_scalar($mConnection) || is_null($mConnection)) {
            if (empty($nExtraKey)) {
                $nExtraKey = $this->nConnectionKey;
            }
            $oConnection = \project\service\database::instance($mConnection, $nExtraKey);
        } elseif (is_object($mConnection) && $mConnection instanceof \core\service\database) {
            $oConnection = $mConnection;
        } else {
            throw new fatalException($this, 'Incorrect connection.');
        }

        $this->oConnection  = $oConnection;
        $this->oDescription = null;
        return $this;
    } // function setConnection
    /**
     * Get Connection of entity
     * @return \core\service\database
     */
    public function getConnection()
    {
        if (!$this->oConnection) {
            $this->setConnection();
        }
        return $this->oConnection;
    } // function getConnection

    /**
     * Set Name of Connection
     * @param string $sConnectionName
     * @return \core\base\model\entity
     */
    public function setConnectionName($sConnectionName)
    {
        $this->sConnectionName = $sConnectionName;
        return $this;
    } // function setConnectionName
    /**
     * Get Name of Connection
     * @return string
     */
    public function getConnectionName()
    {
        return $this->sConnectionName;
    } // function getConnectionName

    /**
     * Set Extra key of Connection
     * @param mixed $nConnectionKey
     * @return \core\base\model\entity
     */
    public function setConnectionKey($nConnectionKey)
    {
        $this->nConnectionKey = $nConnectionKey;
        return $this;
    } // function setConnectionKey
    /**
     * Get Extra key of Connection
     * @return numeric
     */
    public function getConnectionKey()
    {
        return $this->nConnectionKey;
    } // function getConnectionKey
    /**
     * Get Main Parameters
     * @return array
     */
    public function getMainParam()
    {
        return array(
            'collection' => $this->getService()->getCollectionKey(),
            'name'       => $this->getName(),
            'class'      => get_class($this),
            'param'      => $this->aBakParam,
            'connection' => array(
                'name' => $this->getConnectionName(),
                'key'  => $this->getConnectionKey(),
            ),
        );
    } // function getMainParam

    /**
     * Get Entity Name
     * @return string
     */
    public function getName($bShowAlter = false)
    {
        return empty($this->sName) && $bShowAlter ? '(Anonymous)' . $this->getTableName() : $this->sName;
    } // function getName

    /**
     * Get Service of Entity
     * @return \core\service\config\row
     */
    public function getService()
    {
        return $this->oService;
    } // function getService
    /**
     * Get Config of Entity
     * @param string $sKey
     * @param null $mDefault
     * @return \core\service\config\row
     */
    public function getConfig($sKey = null, $mDefault = null)
    {
        return is_null($sKey) ? $this->oConfig : $this->oConfig->get($sKey, $mDefault);
    } // function getConfig

    /**
     * Get SQL-designer
     * @param string $sType
     * @return \core\service\entity\designer
     */
    public function getDesigner($sType = 'select')
    {
        return $this->getService()->getDesigner($this, $sType);
    } // function getDesigner

    /**
     * Get Entity table Description
     * @return \core\service\entity\description
     */
    public function getDescription($aParam = array())
    {
        if (is_null($this->oDescription)) {
            $this->oDescription = $this->getService()->getDescription($this, array_merge($aParam, $this->aBakParam));
        }
        return $this->oDescription;
    } // function getDescription
    /**
     * Get Request Loader
     * @param array $aSQL
     * @return \core\base\model\request
     */
    public function getRequestLoader($aSQL = array())
    {
        if (is_null($this->oRequest)) {
            $sClassName = $this->getRequestClassName();
            $this->oRequest = new $sClassName($this);
        }
        if (!empty($aSQL)) {
            $this->oRequest->setRequests($aSQL);
        }
        return $this->oRequest;
    } // function getRequestLoader

    /**
     * Get ClassName of Row
     * @return srting
     */
    public function getRowClassName()
    {
        if (empty($this->sRowClassName)) {
            $this->sRowClassName = $this->_getClassName('row');
        }
        return $this->sRowClassName;
    } // function getRowClassName
    /**
     * Get ClassName of Rowset
     * @return srting
     */
    public function getRowsetClassName()
    {
        if (empty($this->sRowsetClassName)) {
            $this->sRowsetClassName = $this->_getClassName('rowset');
        }
        return $this->sRowsetClassName;
    } // function getRowsetClassName
    /**
     * Get ClassName of Request
     * @return srting
     */
    public function getRequestClassName()
    {
        if (empty($this->sRequestClassName)) {
            $this->sRequestClassName = $this->_getClassName('request');
        }
        return $this->sRequestClassName;
    } // function getRequestClassName

    /**
     * Get Status of table
     * Result array has next fields:
     *   Name, Engine, Version, Row_format, Rows, Avg_row_length, Data_length,
     *   Max_data_length, Index_length, Index_length, Data_free, Auto_increment,
     *   Create_time, Update_time,Check_time, Collation, Checksum, Create_options, Comment
     * @return array
     */
    public function getTableStatus()
    {
        return $this->getConnection()->getTableStatus($this->sTableName);
    } // function getTableStatus

    /**
     * Get Key for check Is data changed
     * @param integer $iReduce Reduce length key
     * @return string key
     */
    public function getCheckKey($iReduce = 0)
    {
        $aTmp = $this->getTableStatus();
        $sKey = md5(@$aTmp['Rows'] . @$aTmp['Avg_row_length'] . @$aTmp['Data_length'] . @$aTmp['Index_length'] . @$aTmp['Auto_increment'] . @$aTmp['Update_time'] . @$aTmp['Checksum']);
        if($iReduce > 0) {
            return substr($sKey, 0, $iReduce);
        } elseif($iReduce < 0) {
            return substr($sKey, $iReduce);
        } else {
            return $sKey;
        }
    } // function getCheckKey

    // ======== Private/Protected methods ======== \\
    protected function _init($aParam)
    {
        return $this;
    } // function _init

    /**
     * Define Table Name
     * @param array $aParam
     * @return string
     * @throws fatalException
     */
    protected function _defineTableName($aParam = array())
    {
        if (isset($aParam['tableName'])) {
            return $aParam['tableName'];
        }
        $sName = $this->getName();
        $aMatches = array();
        if (preg_match('/^(?:.+\\\\)?(\w+)$/', $sName, $aMatches)) {
            return $aMatches[1];
        }
        throw new fatalException($this, 'Can\'t define the Table name for "' . get_class($this) . '".');
    } // function _defineTableName

    /**
     * Get List of available Property for public access
     * @return array
     */
    protected function _getPropertyList()
    {
        return array(
            'description' => 'getDescription',
            'request'     => 'getRequestLoader',
        );
    } // function _getPropertyList
    /**
     * Set Connection Parameters
     * @param array $aParam
     * @return \core\base\model\entity
     */
    protected function _setConnectionParam($aParam)
    {
        if (isset($aParam['connectionName'])) {
            $this->sConnectionName = $aParam['connectionName'];
        } else {
            $sConnectionName = $this->oConfig['CONNECTION'];
            while (empty($sConnectionName)) {
                $aGlobalConf = \project\service\config::instance('entity')->get('common');
                if (isset($aGlobalConf['CONNECTIONS'])) {
                    $sPrefix = trim($this->getService()->getNsPrefix(), '\\');
                    $nLen    = strlen($sPrefix);
                    $sNS     = get_ns_name($this, 2);
                    for ($i = 0; $i < 2; $i++) {
                        if (isset($aGlobalConf['CONNECTIONS'][$sNS])) {
                            $sConnectionName = $aGlobalConf['CONNECTIONS'][$sNS];
                            break 2;
                        }
                        if (strncmp($sNS, $sPrefix, $nLen) != 0) {
                            break;
                        }
                        $sNS = trim(substr($sNS, $nLen), '\\');
                        if (empty($sNS)) {
                            break;
                        }
                    }
                }
                $sConnectionName = $aGlobalConf['DEFAULT_CONNECTION'];
                break;
            }
            $this->sConnectionName = $sConnectionName;
        }
        $this->nConnectionKey = isset($aParam['connectionKey']) ? (int)$aParam['connectionKey'] : 0;
        return $this;
    } // function _setConnectionParam

    /**
     * Get Class Name for: "rowset", "row", "request"
     * @param string $sKey
     * @return string
     * @throws fatalException
     */
    protected function _getClassName($sKey)
    {
        $sName = $this->getName();
        if (empty($sName)) {
            $sClassName = '';
        } else {
            $sPrefix = $this->getService()->getNsPrefix();
            if (empty($sPrefix)) {
                throw new fatalException($this, 'In config prefix doesn\'t set for "' . $sKey . '".');
            }

            $sClassName = $sPrefix . $sName . '\\' . $sKey;
        }
        if (empty($sClassName) || !class_exists($sClassName)) {
            $sClassName = '\project\base\model\\' . $sKey;
        }

        $oReflection = new \ReflectionClass($sClassName);
        do {
            if($oReflection->getName() == 'core\base\model\\' . $sKey) {
                return $sClassName;
            }
            $oReflection = $oReflection->getParentClass();
        } while(!empty($oReflection));

        throw new fatalException($this, 'Class "' . $sClassName . '" must be instance of "\core\base\model\\' . $sKey . '".');
    } // function _getClassName

    /**
     * Get Sql-request as String
     * @param string|\core\service\entity\designer $mQuery
     * @param mixed $mParam
     * @return string
     * @throws fatalException
     */
    protected function _getSqlAsString($mQuery, $mParam)
    {
        if (is_object($mQuery) && $mQuery instanceof \core\service\entity\designer) {
            return array($mQuery->assemble($mParam), $mQuery->getAdjustedParam());
        } elseif (!is_string($mQuery)) {
            return array($mQuery, $mParam);
        }
        throw new fatalException($this, 'Incorrect format of SQL-request.');
    } // function _getSqlAsString
} // class \core\base\model\entity
?>