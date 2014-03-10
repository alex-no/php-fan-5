<?php namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Entity manager service
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
class entity extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();

    /**
     * @var array Instances of Entity
     */
    private $aEntities = array();

    /**
     * @var mixed Collection Key
     */
    protected $mCollection = null;

    /**
     * Constructor of Service of entity
     * @param mixed $mCollection
     * @throws \fan\project\exception\service\fatal
     */
    protected function __construct($mCollection = 0)
    {
        parent::__construct();

        if (is_null($mCollection)) {
            throw new fatalException($this, 'Collection Key can not be NULL.');
        }
        if (!is_scalar($mCollection)) {
            throw new fatalException($this, 'Collection Key can not be only scalar type.');
        }

        $this->mCollection = $mCollection;
        self::$aInstances[$mCollection] = $this;
    } // function __construct


    // ======== Static methods ======== \\
    /**
     * Get instance of service of entity
     * @param mixed $mCollection Key of collection
     * @return \fan\core\service\entity
     */
    public static function instance($mCollection = 0)
    {
        if (!isset(self::$aInstances[$mCollection])) {
            new self($mCollection);
        }
        return self::$aInstances[$mCollection];
    } // function instance

    // ======== The magic methods ======== \\
    public function __get($sName)
    {
        return $this->get($sName);
    }

    // ======== Main Interface methods ======== \\
    /**
     * Get entity
     * Param keys:
     *   'connectionName', 'connectionKey', 'cacheEnabled',
     *   'tableName', 'primeryKey', 'fields', 'keys', 'relations',
     *   ''
     * @param string $sName
     * @param array $aParam
     * @return \fan\core\base\model\entity
     * @throws \fan\project\exception\service\fatal
     */
    public function get($sName, $aParam = array())
    {
        if (!isset($this->aEntities[$sName])) {
            $sPrefix = $this->getNsPrefix();
            if (substr($sName, 0, strlen($sPrefix)) == $sPrefix) {
                $sClass = $sName . '\entity';
                $sName  = substr($sName, strlen($sPrefix));
            } else {
                $sName = trim($sName, '\\');
                $sClass = $sPrefix . $sName . '\entity';
            }
            $this->aEntities[$sName] = $this->_getEntity($sClass, $aParam, $sName);
        }
        return $this->aEntities[$sName];
    }

    /**
     * Get Anonymous entity
     *   Note: Anonymous entity doesn't have name
     * @param string $sClass
     * @param array $aParam
     * @return \fan\core\base\model\entity
     */
    public function getAnonymous($sClass, $aParam = array())
    {
        return $this->_getEntity($sClass, $aParam);
    }

    /**
     * Get Object of Entity By Name of Table in DB
     * @param string $sTableName
     * @param string $sConnectionName
     * @param boolean $bForce - do not use cache
     * @return \fan\core\base\model\entity|null
     */
    public function getEntityByTable($sTableName, $sConnectionName = null, $bForce = false)
    {
        $sName = $this->_getNameByTable($sTableName, $sConnectionName, $bForce);
        if (empty($sName)) {
            return null;
        }
        try {
            $oEtt  = $this->get($sName);
        } catch (fatalException $e) {
            return null;
        }
        return $oEtt;
    }

    /**
     * Name of directory with SQL-requests
     * @return string
     */
    public function getSqlDir()
    {
        $sDir = $this->_getConfigParam('SQL_DIR');
        return empty($sDir) ? 'sql' : $sDir;
    }

    /**
     * Get namespace prefix of all entity classes
     * @return string
     */
    public function getNsPrefix()
    {
        $sPrefix = $this->_getConfigParam('NS_PREFIX');
        return empty($sPrefix) ? '\fan\model\\' : '\\' . trim($sPrefix, '\\') . '\\';
    }
    /**
     * Get namespace suffix of entity "file_data", "image", "flash", "video", etc
     * @return string
     */
    public function getFileNsSuffix()
    {
        $sSuffix = $this->_getConfigParam('FILE_NS_SUFFIX');
        return empty($sSuffix) ? '' : trim($sSuffix, '\\') . '\\';
    } // function getFileNsSuffix

    /**
     * Get Collection Key
     * @return string|integer
     */
    public function getCollectionKey()
    {
        return $this->mCollection;
    } // function getCollectionKey

    /**
     * Get Entity table Description
     * @param \fan\core\base\model\entity $oEntity
     * @param array $aParam
     * @return \fan\core\service\entity\description
     */
    public function getDescription(\fan\core\base\model\entity $oEntity, $aParam = array())
    {
        return new \fan\project\service\entity\description($oEntity, $aParam);
    } // function getDescription

    /**
     * Get SQL-designer
     * @param \fan\core\base\model\entity $oEntity
     * @param string $sType
     * @return \fan\core\service\entity\designer
     * @throws \fan\project\exception\service\fatal
     */
    public function getDesigner(\fan\core\base\model\entity $oEntity, $sType = 'select')
    {
        $sClassName = '\fan\project\service\entity\designer\\' . $sType;
        if (!class_exists($sClassName)) {
            throw new fatalException($this, 'Class of SQL-designer "' . $sType . '" doesn\'t exist.');
        }
        return new $sClassName($oEntity);
    } // function getDesigner

    /**
     * Make new object of Snippet
     * @param \fan\core\service\entity\designer\snippety $oSnippety
     * @param type $sQuery
     * @param type $sSrcCondition
     * @param type $sCallback
     * @return \fan\project\service\entity\snippet
     */
    public function getSnippet(\fan\core\service\entity\designer\snippety $oSnippety, $sQuery, $sSrcCondition, $sCallback)
    {
        return new \fan\project\service\entity\snippet($oSnippety, $sQuery, $sSrcCondition, $sCallback);
    } // function getSnippet

    /**
     * Get Encapsulant
     * @param string $sClass
     * @return \fan\core\service\entity\encapsulant\simple
     */
    public function getEncapsulant($sClass = null)
    {
        $sClass = '\fan\project\service\entity\encapsulant\\' . ($sClass ? $sClass : $this->getConfig('encapsulantClass', 'simple'));
        return new $sClass($this);
    } // function getEncapsulant

    // ======== Private/Protected methods ======== \\
    /**
     * Get Entity
     * @param string $sClass
     * @param array $aParam
     * @param string $sName
     * @return \fan\core\service\entity
     * @throws fatalException
     */
    protected function _getEntity($sClass, $aParam, $sName = null)
    {
        if (!class_exists($sClass)) {
            throw new fatalException($this, 'Undefind entity "' . $sClass . '"');
        }

        $oEntity = new $sClass($this, $sName, $aParam);
        if (!$oEntity instanceof \fan\core\base\model\entity) {
            throw new fatalException($this, 'Entity "' . (empty($sName) ? $sClass : $sName) . '" must be instance of "\fan\core\base\model\entity"');
        }
        return $oEntity;
    } // function _getEntity

    /**
     * Get parameter from the config taking into account current collection name
     * @param string $sKey
     * @return mixed
     */
    protected function _getConfigParam($sKey)
    {
        $mData0 = $this->getConfig($sKey, array());
        $oExtraConf = $this->getConfig(array('COLLECTION', $this->getCollectionKey()));
        $mData1 = is_object($oExtraConf) ? $oExtraConf->get($sKey) : null;
        return empty($mData1) ? $mData0 : $mData1;
    } // function _getConfigParam

    /**
     * Get entity name by table name
     * @param string $sKey
     * @return mixed
     */
    protected function _getNameByTable($sTableName, $sConnectionName, $bForce)
    {
        $aData = $bForce ? array() : $this->_getCacheData('reverce_link', array());

        // Try to pull entity name from the cache
        if (!empty($sConnectionName) && isset($aData[$sConnectionName][$sTableName])) {
            return $aData[$sConnectionName][$sTableName];
        }
        if (empty($sConnectionName) && !empty($aData)) {
            foreach ($aData as $v) {
                if (isset($v[$sTableName])) {
                    return $v[$sTableName];
                }
            }
        }

        // Make New data by FileSystem
        $sNs   = rtrim($this->getNsPrefix(), '\\');
        $aDirs = array(
            $sNs => \bootstrap::getLoader()->getPathByNS($sNs),
        );

        while (!empty($aDirs)) {
            reset($aDirs);
            $sNs   = key($aDirs);
            $sDir  = array_shift($aDirs);
            $aList = scandir($sDir);
            foreach ($aList as $v) {
                $sCheck = $sDir . '/' . $v;
                if ($v != '.' && $v != '..' && is_dir($sCheck)) {
                    if (file_exists($sCheck . '/entity.php')) {
                        try {
                            $oEtt = $this->get($sNs . '\\' . $v);
                        } catch (fatalException $e) {
                            continue;
                        }
                        $aData[$oEtt->getConnectionName()][$oEtt->getTableName()] = $oEtt->getName();
                    } else {
                        $aDirs[$sNs . '\\' . $v] = $sCheck;
                    }
                }
            }
        }

        // Save new data to the cache and return requested value
        $this->_setCacheData('reverce_link', $aData);
        return array_val($aData, array($sConnectionName, $sTableName));
    } // function _getNameByTable

} // class \fan\core\service\entity
?>