<?php namespace core\service;
use project\exception\service\fatal as fatalException;
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
 * @version of file: 05.002 (17.12.2013)
 */
class entity extends \core\base\service\multi
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
     * @throws \project\exception\service\fatal
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
     * @return \core\service\entity
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
     * @return \core\base\model\entity
     * @throws \project\exception\service\fatal
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
     * @return \core\base\model\entity
     */
    public function getAnonymous($sClass, $aParam = array())
    {
        return $this->_getEntity($sClass, $aParam);
    }

    /**
     *
     * @return type
     */
    public function getSqlDir()
    {
        $sDir = $this->_getConfigParam('SQL_DIR');
        return empty($sDir) ? 'sql' : $sDir;
    }

    public function getNsPrefix()
    {
        $sPrefix = $this->_getConfigParam('NS_PREFIX');
        return empty($sPrefix) ? '\project\model\\' : '\\' . trim($sPrefix, '\\') . '\\';
    }

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
     * @param \core\base\model\entity $oEntity
     * @param array $aParam
     * @return \core\service\entity\description
     */
    public function getDescription(\core\base\model\entity $oEntity, $aParam = array())
    {
        return new \project\service\entity\description($oEntity, $aParam);
    } // function getDescription

    /**
     * Get SQL-designer
     * @param \core\base\model\entity $oEntity
     * @param string $sType
     * @return \core\service\entity\designer
     * @throws \project\exception\service\fatal
     */
    public function getDesigner(\core\base\model\entity $oEntity, $sType = 'select')
    {
        $sClassName = '\project\service\entity\designer\\' . $sType;
        if (!class_exists($sClassName)) {
            throw new fatalException($this, 'Class of SQL-designer "' . $sType . '" doesn\'t exist.');
        }
        return new $sClassName($oEntity);
    } // function getDesigner

    /**
     * Make new object of Snippet
     * @param \core\service\entity\designer\snippety $oSnippety
     * @param type $sQuery
     * @param type $sSrcCondition
     * @param type $sCallback
     * @return \project\service\entity\snippet
     */
    public function getSnippet(\core\service\entity\designer\snippety $oSnippety, $sQuery, $sSrcCondition, $sCallback)
    {
        return new \project\service\entity\snippet($oSnippety, $sQuery, $sSrcCondition, $sCallback);
    } // function getSnippet

    /**
     * Get Encapsulant
     * @param string $sClass
     * @return \core\service\entity\encapsulant\simple
     */
    public function getEncapsulant($sClass = null)
    {
        $sClass = '\project\service\entity\encapsulant\\' . ($sClass ? $sClass : $this->getConfig('encapsulantClass', 'simple'));
        return new $sClass($this);
    } // function getEncapsulant

    // ======== Private/Protected methods ======== \\
    protected function _getEntity($sClass, $aParam, $sName = null)
    {
        if (!class_exists($sClass)) {
            throw new fatalException($this, 'Undefind entity "' . $sClass . '"');
        }

        $oEntity = new $sClass($this, $sName, $aParam);
        if (!$oEntity instanceof \core\base\model\entity) {
            throw new fatalException($this, 'Entity "' . (empty($sName) ? $sClass : $sName) . '" must be instance of "\core\base\model\entity"');
        }
        return $oEntity;
    } // function _getEntity

    protected function _getConfigParam($sKey)
    {
        $mData0 = $this->getConfig($sKey, array());
        $oExtraConf = $this->getConfig(array('COLLECTION', $this->getCollectionKey()));
        $mData1 = is_object($oExtraConf) ? $oExtraConf->get($sKey) : null;
        return empty($mData1) ? $mData0 : $mData1;
    } // function _getConfigParam

} // class \core\service\entity
?>