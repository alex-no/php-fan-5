<?php namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Loader of Source SQL-requests for \fan\core\service\entity\designer\request
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
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class request
{
    /**
     * Saved data
     * @var array
     */
    protected $aSQL = array();

    /**
     * Entity - table data
     * @var \fan\core\base\model\entity
     */
    protected $oEntity = null;

    /**
     * Row-data constructor
     * @param \fan\core\base\model\entity $oEntity
     * @param array $aData
     */
    public function __construct(\fan\core\base\model\entity $oEntity)
    {
        $this->oEntity = $oEntity;
    } // function __construct

    // ======== The magic methods ======== \\

    public function __set($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    }

    public function __get($sKey)
    {
        return $this->get($sKey);
    }
    /**
     * Call to unset entity method
     * @param string $sMethod method name
     * @param array $aArgs arguments
     * @return mixed Value return by engine
     * @throws fatalException
     */
    public function __call($sMethod, $aArgs)
    {
        if(substr($sMethod, 0, 4) == 'set_') {
            $this->set(substr($sMethod, 4), isset($aArgs[0]) ? $aArgs[0] : null);
        } elseif (substr($sMethod, 0, 4) == 'get_') {
            return $this->get(substr($sMethod, 4), isset($aArgs[0]) ? $aArgs[0] : null, isset($aArgs[1]) ? $aArgs[1] : false);
        } else {
            throw new fatalException($this->getEntity(), 'Incorrect call of instance SQL-request loader!');
        }
    } // function __call

    // ======== Required Interface methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * Get value of data
     * @param string $sKey
     * @return string
     */
    public function get($sKey)
    {
        if (!array_key_exists($sKey, $this->aSQL)) {
            $this->aSQL[$sKey] = $this->_loadSQL($sKey);
        }
        if (!$this->aSQL[$sKey]) {
            trigger_error('Call for unset SQL-key.', E_USER_WARNING);
            return null;
        }
        return $this->aSQL[$sKey];
    } // function get

    /**
     * Set SQL
     * @param string $sKey
     * @param string $sValue
     * @return \fan\core\base\model\request
     */
    public function set($sKey, $sValue)
    {
        $this->aSQL[$sKey] = $sValue;
        return $this;
    } // function set

    /**
     * Set several Requests (usualy at the start
     * @param array $aSQL
     * @return \fan\core\base\model\request
     */
    public function setRequests($aSQL)
    {
        $this->aSQL = array_merge($this->aSQL, $aSQL);
        return $this;
    } // function setRequests

    /**
     * Gets All Fields by array
     * @return array
     */
    public function toArray()
    {
        return $this->aSQL;
    } // function toArray

    /**
     * Get instace of Entity
     * @return \fan\core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    // ======== Private/Protected methods ======== \\
    /**
     * Load the SQL query from a file by key
     * @param string $sKey array's key
     * @return string SQL query
     */
    protected function _loadSQL($sKey)
    {
        $sFileName = $this->_checkSQLfile($sKey);
        return is_null($sFileName) ? null : file_get_contents($sFileName);
    } // function _loadSQL

    /**
     * Check the SQL-file is exist
     * @param string $sKey array's key
     * @return string file-path
     */
    protected function _checkSQLfile($sKey)
    {
        $oEntity = $this->getEntity();
        $sDirName = $oEntity->getService()->getSqlDir();
        foreach (service('reflector')->getParentPaths($oEntity) as $v) {
            $sFileName  = pathinfo($v, PATHINFO_DIRNAME) . '/';
            $sFileName .= $sDirName . '/' . $sKey . '.sql';
            if (file_exists($sFileName)) {
                return $sFileName;
            }
        }
        return null;
    } // function _checkSQLfile

} // class \fan\core\base\model\request
?>