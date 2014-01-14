<?php namespace core\service\entity;
use project\exception\model\entity\fatal as fatalException;
/**
 * Entity table-description
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
 * @version of file: 05.005 (14.01.2014)
 *
 * @property-read string|array $primeryKey
 * @method \core\service\entity\description setPrimeryKey() setPrimeryKey(array|string $mKey)
 * @method mixed getPrimeryKey()
 * @property-read array $fields
 * @method \core\service\entity\description setFields() setFields(array $aFields)
 * @method mixed getFields()
 * @property-read array $keys
 * @method \core\service\entity\description setKeys() setKeys(array $aKeys)
 * @method mixed getKeys()
 * @property-read array $relations
 * @method mixed getRelations()
 * @property-read array $dependents
 * @method \core\service\entity\description setDependents()  setDependents(array $aDependents)
 * @method mixed getDependents()
 * @property-read string $engine
 * @method string getEngine()
 * @property-read string $createTime
 * @method string getCreateTime()
 * @property-read string $tableCollation
 * @method string getTableCollation()
 * @property string $comment
 * @method string getComment()
 */
class description
{
    /**
     * Entity - owner of table description
     * @var \core\base\model\entity
     */
    protected $oEntity = null;
    /**
     * Table descriptor (Maker dynamic description)
     * @var \core\service\entity\descriptor
     */
    protected $oDescriptor = null;

    /**
     * List of applied property avaylable by magic functions
     * @var array
     */
    protected $aProperty = array(
        'primeryKey'     => null,
        'fields'         => null,
        'keys'           => null,
        'relations'      => null,
        //'dependents'     => null,
        'engine'         => null,
        'createTime'     => null,
        'tableCollation' => null,
        'comment'        => null,
    );

    /**
     * Enable save/load cache of entity structure
     * @var boolean
     */
    protected $bCacheEnabled = true;
    /**
     * List of Property which set dynamically
     * @var array
     */
    protected $aDynamicProperty = array();

    /**
     * Description constructor
     * @param \core\base\model\entity $oEntity
     * @param array $aParam
     */
    public function __construct(\core\base\model\entity $oEntity, $aParam)
    {
        $this->oEntity = $oEntity;

        $this->bCacheEnabled    = isset($aParam['cacheEnabled']) ? !empty($aParam['cacheEnabled']) : $oEntity->getConfig()->get('cacheEnabled', true);
        $this->aDynamicProperty = $this->_defineDynamicProperty($aParam);
    } // function __construct
    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    /**
     * Set virtual property
     * @param string $sKey
     * @param mixed $mValue
     */
    public function __set($sKey, $mValue)
    {
        $this->set($sKey, $mValue);
    } // function __set

    /**
     * Get virtual property
     * @param string $sKey
     * @return mixed
     */
    public function __get($sKey)
    {
        $mRet = $this->get($sKey);
        return is_string($mRet) ? (string)$mRet : $mRet; //ToDo: Examine this hack
    } // function __get

    /**
     * Call to unset entity method
     * @param string $sMethod method name
     * @param array $aArgs arguments
     * @return mixed Value return by engine
     */
    public function __call($sMethod, $aArgs)
    {
        $sKey = lcfirst(substr($sMethod, 3));
        if(substr($sMethod, 0, 3) == 'set' && $this->_checkPropertyName($sKey, false)) {
            $this->set($sKey, $aArgs[0]);
            return $this;
        } elseif (substr($sMethod, 0, 3) == 'get' && $this->_checkPropertyName($sKey, false)) {
            return $this->get($sKey, isset($aArgs[0]) ? $aArgs[0] : false);
        }
        throw new fatalException($this->getEntity(), 'Incorrect call of entity description!');
    } // function __call

    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Get value of property
     * @param string $sKey
     * @return string
     */
    public function get($sKey, $bForce = false)
    {
        if ($this->_checkPropertyName($sKey)) {
            if (is_null($this->aProperty[$sKey])) {
                $this->_loadDynamicProperty($sKey, $bForce);
            }
            return $this->aProperty[$sKey];
        }
        return null;
    } // function get

    /**
     * Set value of property
     * @param string $sKey
     * @param mixed $mValue
     * @return \core\service\entity\description
     */
    public function set($sKey, $mValue)
    {
        if ($this->_checkPropertyName($sKey) && ($sKey == 'comment' || is_null($this->aProperty[$sKey]))) {
            $sMethod = 'set' . ucfirst($sKey);
            if (method_exists($this, $sMethod)) {
                $this->$sMethod($mValue);
            } else {
                $this->aProperty[$sKey] = $mValue;
            }
        }
        return $this;
    } // function set

    /**
     * Set Comment
     * @param string $sValue
     * @return \core\service\entity\description
     */
    public function setComment($sValue)
    {
        $this->getEntity()->getConnection()->execute('ALTER TABLE `' . $this->getTableName() . '` COMMENT = ?', array($sValue));
        if ($this->bCacheEnabled) {
            $this->_loadDynamicProperty();
        }
        $this->aProperty['comment'] = (string)$sValue;
        $this->_saveCacheFile();
        return $this;
    } // function setComment

    /**
     * Check - is Table exists in DB
     * @return boolean
     */
    public function isTableExists()
    {
        return $this->_getDescriptor()->isTableExists();
    } // function isTableExists

    /**
     * Get Table Name
     * @return string
     */
    public function getTableName()
    {
        return $this->getEntity()->getTableName();
    } // function getTableName

// ToDo: Set dependents by entity-class/method (OR it will by recognized by DB-field automatically)

    /**
     * Gets All Property by array
     * @return array
     */
    public function toArray()
    {
        return $this->aProperty;
    } // function toArray

    /**
     * Get instace of Entity
     * @return \core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity
    // ======== Private/Protected methods ======== \\

    /**
     *
     * @param string $sPropName
     * @param boolean $bAllowException
     * @return boolean
     * @throws fatalException
     */
    protected function _checkPropertyName($sPropName, $bAllowException = true)
    {
        if (array_key_exists($sPropName, $this->aProperty)) {
            return true;
        }
        if ($bAllowException) {
            throw new fatalException($this->getEntity(), 'Incorret Property Name of Entity-desckription "' . $sPropName . '".');
        }
        return false;
    } // function _checkPropertyName

    /**
     * Get Table Descriptor (Several classes for different type of description)
     * @return \core\service\entity\descriptor
     */
    protected function _getDescriptor()
    {
        if (is_null($this->oDescriptor)) {
            // ToDo: Define descriptor by type of current connection
            try {
                $this->oDescriptor = new \project\service\entity\descriptor\mysql\schema($this);
            } catch (\core\exception\model\reverse $e) {
                $this->oDescriptor = new \project\service\entity\descriptor\mysql\direct($this);
            }
        }
        return $this->oDescriptor;
    } // function _getDescriptor

    /**
     * Define array of keys for Property will be set Dynamically
     * @param array $aParam
     * @return array
     */
    protected function _defineDynamicProperty($aParam = array())
    {
        $aResult = array();
        foreach ($this->aProperty as $k => $v) {
            if (isset($aParam[$k])) {
                $this->aProperty[$k] = $aParam[$k]; // ToDo: Validate $aParam[$k] before set it
            } elseif (is_null($v)) {
                $aResult[] = $k;
            }
        }
        return $aResult;
    } // function _defineDynamicProperty

    /**
     * Load Dynamic Property
     * @return \core\base\model\entity
     */
    protected function _loadDynamicProperty($sKey = null, $bForce = false)
    {
        $sCacheFile = $this->_getCacheFileName();
        if ($this->bCacheEnabled && file_exists($sCacheFile) && !$bForce) {
            $aProperty = include $sCacheFile;
            foreach ($this->aDynamicProperty as $k => $v) {
                $this->aProperty[$v] = isset($aProperty[$v]) ? $aProperty[$v] : null;
                unset($this->aDynamicProperty[$k]);
            }
        } elseif ($this->bCacheEnabled || empty($sKey)) {
            if (!$this->isTableExists()) {
                throw new fatalException($this->getEntity(), 'DB table "' . $this->getTableName() . '" doesn\'t exists.');
            }
            $oDescriptor = $this->_getDescriptor();
            foreach ($this->aProperty as $k => $v) {
                $sMethod = 'get' . ucfirst($k);
                $this->aProperty[$k] = $oDescriptor->$sMethod();
            }
            $this->_saveCacheFile();
        } else {
            $sMethod = 'get' . ucfirst($sKey);
            $this->aProperty[$sKey] = $this->_getDescriptor()->$sMethod();
        }
        return $this;
    } // function _loadDynamicProperty

    /**
     * Save Cache File
     * @return \core\base\model\entity
     */
    protected function _saveCacheFile()
    {
        if ($this->bCacheEnabled) {
            $aSavedData = array('class' => get_class($this->getEntity()));
            $aSavedData = array_merge($aSavedData, $this->aProperty);
            $sCacheFile = $this->_getCacheFileName();
            file_put_contents($sCacheFile, '<?php
/*
 * Entity structure array
 */
return ' . var_export($aSavedData, true) . ';
?>');
        }
        return $this;
    } // function _saveCacheFile
    /**
     *Get Cache FileName
     * @return string
     */
    protected function _getCacheFileName()
    {
        $aParam    = $this->getEntity()->getConnection()->getConnectionParam();
        $sCacheDir = rtrim($this->getEntity()->getService()->getConfig('CACHE_DIR', '{TEMP}/cache/entity'), '\\/') . '/';
        $sCacheDir = \bootstrap::parsePath($sCacheDir);
        return $sCacheDir . $this->getTableName() . '_' . md5($aParam['HOST'] . $aParam['DATABASE']);
    } // function _getCacheFileName
} // class \core\service\entity\description
?>