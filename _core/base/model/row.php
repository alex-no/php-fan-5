<?php namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Description of row
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class row implements \ArrayAccess, \Serializable
{
    /**
     * Saved data
     * @var array
     */
    protected $aSrcData = array();
    /**
     * Saved data
     * @var array
     */
    protected $aData = array();
    /**
     * Changed fields
     * @var array
     */
    protected $aChanged = array();

    /**
     * Object of Entity Class
     * @var \fan\core\base\model\entity
     */
    protected $oEntity = null;
    /**
     * Object of Rowset Class
     * @var \fan\core\base\model\rowset
     */
    protected $oRowset = null;

    /**
     * Field Info
     * @var array
     */
    protected $aFieldInfo = array();

    /**
     * This property true if data is load
     * @var boolean
     */
    protected $bIsDataLoad = false;
    /**
     * This property true if Init Id Only without Loading (Used for update part/full of Row-data)
     * @var boolean
     */
    protected $bInitIdOnly = false;

    /**
     * @var string Current local field suffix
     */
    protected $sCurrentLocal;
    /**
     * @var string Default local field suffix
     */
    protected $sDefaultLocal;

    /**
     * Flag for show error message
     * @var boolean
     */
    protected $bShowError = true;

    /**
     * Row-data constructor
     * @param \fan\core\base\model\entity $oEntity
     * @param array $aData
     */
    public function __construct(\fan\core\base\model\entity $oEntity, &$aData = array(), \fan\core\base\model\rowset $oRowset = null)
    {
        $this->oEntity = $oEntity;
        $this->oRowset = $oRowset;
        $this->_fixLoadedData($aData);

        $this->_restoreProperties();
    } // function __construct

    // ======== Methods for redefine in children classes ======== \\

    /**
     * This method will be run after entity is loaded
     */
    protected function _runAfterLoad()
    {
    }
    /**
     * This method will be run after entity load is fail
     */
    protected function _runAfterLoadFail()
    {
    }
    /**
     * This method will be run before entity Insert new record
     */
    protected function _runBeforeInsert()
    {
    }
    /**
     * This method will be run before entity Update record
     */
    protected function _runBeforeUpdate()
    {
    }
    /**
     * This method will be run after entity Insert new record
     */
    protected function _runAfterInsert($aChanged)
    {
    }
    /**
     * This method will be run after entity Update record
     */
    protected function _runAfterUpdate($aChanged)
    {
    }
    /**
     * This method will be run after entity Save (Insert/Update) record
     */
    protected function _runAfterSave($aChanged)
    {
    }
    /**
     * This method will be run after entity record is deleted
     * @param mixed $mDelId - deleted ID
     */
    protected function _runAfterDelete($mDelId)
    {
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * Get Config of Entity
     * @param string $sKey
     * @param null $mDefault
     * @return \fan\core\service\config\row
     */
    public function getConfig($sKey = null, $mDefault = null)
    {
        return $this->getEntity()->getConfig($sKey, $mDefault);
    } // function getConfig

    /**
     * Load current entity by ID
     * @param mixed $mRowId
     * @param boolean $bIdIsEncrypt
     * @return \fan\core\base\model\row
     */
    public function loadById($mRowId, $bIdIsEncrypt = false)
    {
        $mParam = is_null($mRowId) ? null : $this->getEntity()->getParamById($mRowId, $bIdIsEncrypt);
        $this->loadByParam($mParam, 0, null);
        return $this;
    } // function loadById

    /**
     * Load current entity by parameters
     * @param array|object $mParam
     * @param numeric $nOffset
     * @param string $sOrderBy
     * @return \fan\core\base\model\row
     */
    public function loadByParam($mParam, $nOffset = 0, $sOrderBy = null)
    {
        $oEtt = $this->getEntity();
        if (!empty($this->aChanged)) {
            $sName = $oEtt->getName();
            trigger_error('Load new data for changed row. Entity "' . $sName . '". Id=' . $this->getId(false), E_USER_NOTICE);
        } elseif ($this->bIsDataLoad) {
            $sName = $oEtt->getName();
            trigger_error('Load new data for loaded row. Entity "' . $sName . '". Id=' . $this->getId(false), E_USER_NOTICE);
        }

        if (is_null($mParam)) {
            $aData = array();
        } else {
            $aData =& $oEtt->getDataByParam($mParam, 1, $nOffset, $sOrderBy, true);
        }
        $this->_fixLoadedData($aData);
        return $this;
    } // function loadByParam

    /**
     * Init Id Only without Loading (only for update part/full of Row-data)
     * @param type $mRowId
     * @return \fan\core\base\model\row
     * @throws fatalException
     */
    public function initIdOnly($mRowId)
    {
        if ($this->bIsDataLoad) {
            throw new fatalException($this->getEntity(), 'Call "initIdOnly"-method for loaded data!');
        }
        $mPrimeryKey = $this->getEntity()->description->getPrimeryKey();
        if (is_string($mPrimeryKey)) {
            $this->set($mPrimeryKey, $mRowId, false);
        } else {
            foreach ($mPrimeryKey as $k) {
                $this->set($k, $mRowId[$k], false);
            }
        }
        $this->bInitIdOnly = true;
        return $this;
    } // function initIdOnly

    /**
     * Get value of data
     * @param number $sFieldName
     * @return mixed
     */
    public function get($sFieldName, $mDefaultVal = null, $bAllowException = true)
    {
        $sFullFieldName = $sFieldName;
        if ($sFieldName{0} == '{' && substr($sFieldName, -1) == '}') {
            $sFieldName = substr($sFieldName, 1, -1);
            if (array_key_exists($sFieldName . $this->_getCurrentLocal(), $this->aData)) {
                $sFullFieldName = $sFieldName . $this->_getCurrentLocal();
            } elseif (array_key_exists($sFieldName . $this->_getDefaultLocal(), $this->aData)) {
                $sFullFieldName = $sFieldName . $this->_getDefaultLocal();
            }
        }

        $sMethod = 'get_' . $sFullFieldName;
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod($mDefaultVal, $bAllowException);
        }
        if ($sFieldName != $sFullFieldName) {
            $sMethod = 'get_' . $sFieldName;
            if (method_exists($this, $sMethod)) {
                return $this->$sMethod($mDefaultVal, $bAllowException);
            }
        }
        return $this->_getFieldValue($sFullFieldName, $mDefaultVal, $bAllowException);
    } // function get
    /**
     * Get value of row be Name and current local
     * @param string $sName
     * @param mixed $mDefaultVal
     * @param boolean $bAllowException
     * @return mixed
     */
    public function getByLocal($sName, $mDefaultVal = null, $bAllowException = true)
    {
        return $this->get('{' . $sName . '}', $mDefaultVal, $bAllowException);
    }

    /**
     * Set value of data
     * @param array|entity $mValue
     * @return \fan\core\base\data
     */
    public function set($sFieldName, $mValue, $bAllowException = true)
    {
        $sFullFieldName = $sFieldName;

        if ($sFieldName{0} == '{' && substr($sFieldName, -1) == '}') {
            $sFieldName = substr($sFieldName, 1, -1);
            $sFullFieldName = $sFieldName . $this->_getCurrentLocal();
        }

        // Set main value of field by one of ways
        do {
            $sMethod  = 'set_' . $sFullFieldName;
            if(method_exists($this, $sMethod)) {
                $this->$sMethod($mValue, $bAllowException);
                break;
            } elseif ($sFullFieldName != $sFieldName) {
                $sMethod  = 'set_' . $sFieldName;
                if(method_exists($this, $sMethod)) {
                    $this->$sMethod($mValue, $bAllowException);
                    break;
                }
            }
            $this->_setFieldValue($sFullFieldName, $mValue, $bAllowException);
        } while (false);

        // If New row - duplicate value for default local if it isn't set
        if (!$this->bIsDataLoad && $sFullFieldName != $sFieldName) {
            $sDefaultFieldName = $sFieldName . $this->_getDefaultLocal();
            if (!isset($this->aData[$sDefaultFieldName])) {
                $this->_setFieldValue($sDefaultFieldName, $this->aData[$sFullFieldName], $bAllowException);
            }
        }

        return $this;
    } // function set

    /**
     * Set some localized field by current local
     * @param string $sName
     * @param mixed $mValue
     * @param boolean $bAllowException
     * @return \fan\core\base\data
     */
    public function setByLocal($sName, $mValue = null, $bAllowException = true)
    {
        return $this->set('{' . $sName . '}', $mValue, $bAllowException);
    } // function setByLocal

    /**
     * Gets All Fields by array
     * @return array Fields
     */
    public function toArray()
    {
        return $this->getFields(null, true);
    } // function toArray
    /**
     * Gets All Fields by array
     * @return array Fields
     */
    public function getFields($mKeys = null, $bAllExists = true)
    {
        if (is_null($mKeys)) {
            if ($bAllExists) {
                $mKeys = array_keys($this->aData);
            } else {
                $aInfo = $this->_getFullFieldsInfo();
                $mKeys = empty($aInfo) ? array() : array_keys($aInfo);
            }
        } elseif (!is_array_alt($mKeys)) {
            if (!is_scalar($mKeys)) {
                throw new fatalException($this->getEntity(), 'Incorrect Field Keys.');
            }
            $mKeys = array($mKeys);
        }

        $aResult = array();
        foreach($mKeys as $k) {
            $aResult[$k] = $this->get($k, null, $this->bIsDataLoad);
        }
        return $aResult;
    } // function getFields

    /**
     * Set All Fields together
     * @param array $aFields field -> value pairs
     * @param boolean $bIsSave
     * @return \fan\core\base\model\row
     */
    public function setFields($aFields, $bIsSave = false)
    {
        if (is_array($aFields)) {
            foreach ($aFields as $sFieldName => $mValue) {
                $this->set($sFieldName, $mValue);
            }
        }
        if ($bIsSave) {
            $this->save();
        }
        return $this;
    } // function setFields

    /**
     * Get Row from Top linked table
     * @param string $sByField
     * @param boolean $bLogEmptyVal
     * @return \fan\core\base\model\row
     */
    public function getTopRow($sByField, $bLogEmptyVal = false)
    {
        $oErr = \fan\project\service\error::instance();
        /* @var $oErr \fan\core\service\error */
        $sErrHeader = 'Error while get Top Row';
        $mVal = $this->get($sByField, null, false);
        if (empty($mVal)) {
            if ($bLogEmptyVal) {
                $oErr->logErrorMessage('Value for field "' . $sByField . '" is empty', $sErrHeader);
            }
            return null;
        }

        $oEtt = $this->oEntity;
        $aTmp = $oEtt->description->relations;
        $aRel = null;
        foreach ($aTmp as $v) {
            if ($v['field'] == $sByField) {
                $aRel = $v;
                break;
            }
        }
        if (empty($aRel)) {
            $oErr->logErrorMessage('Incorrect linked field "' . $sByField . '"', $sErrHeader);
            return null;
        }

        $oTopEtt = $oEtt->getService()->getEntityByTable($aRel['ref_table'], $oEtt->getConnectionName());
        if (empty($oTopEtt)) {
            $oErr->logErrorMessage('Linked entity for field "' . $sByField . '" is not found', $sErrHeader);
            return null;
        }
        return $oTopEtt->getRowByParam(array($v['ref_field'] => $mVal));
    } // function getTopRow

    /**
     * Get Rowset from linked tables
     * @param string $sByField
     * @return \fan\core\base\model\row
     */
    public function getBottomRowset($sTableName, $nQtt = -1, $nOffset = -1, $sOrderBy = '')
    {
        $oErr = \fan\project\service\error::instance();
        /* @var $oErr \fan\core\service\error */
        $sErrHeader = 'Error while get Bottom Rowset';

        $oCurEtt    = $this->getEntity();
        $oBottomEtt = $oCurEtt->getService()->getEntityByTable($sTableName, $oCurEtt->getConnectionName());
        if (empty($oBottomEtt)) {
            $oErr->logErrorMessage('Can\'t get entity for table "' . $sTableName . '"', $sErrHeader);
            return null;
        }

        $aTmp = $oBottomEtt->description->relations;
        $aRel = null;
        foreach ($aTmp as $v) {
            if ($v['ref_table'] == $oCurEtt->getTableName()) {
                $aRel = $v;
                break;
            }
        }
        if (empty($aRel)) {
            $oErr->logErrorMessage('Incorrect linked table "' . $sTableName . '"', $sErrHeader);
            return null;
        }

        return $oBottomEtt->getRowsetByParam(array($v['field'] => $this->getId()), $nQtt, $nOffset, $sOrderBy);
    } // function getBottomRowset

    /**
     * Revert all changes of this row
     * @return \fan\core\base\model\row
     */
    public function revert()
    {
        foreach ($this->aSrcData as $sFieldName => $mValue) {
            $this->aData[$sFieldName] = $mValue;
        }
        $this->aChanged = array();
        return $this;
    } // function revert

    /**
     * Save this row
     * @return \fan\core\base\model\row
     */
    public function save()
    {
        $aChanged = $this->aChanged;
        if (!empty($aChanged)) {
            if ($this->bIsDataLoad || $this->bInitIdOnly) {
                $this->_runBeforeUpdate();
                $this->_updateRow();
                $this->_runAfterUpdate($aChanged);
            } else {
                $this->_runBeforeInsert();
                $this->_insertRow();
                $this->_runAfterInsert($aChanged);
            }
            $this->_runAfterSave($aChanged);
            //\fan\project\service\cache::instance()->clearCacheByEntity($this);
        }
        return $this;
    } // function save

    /**
     * Delete record
     * @return boolean
     */
    public function delete()
    {
        if ($this->bIsDataLoad) {
            $oEtt   = $this->getEntity();
            $mDelId = $this->getId(false, true);
            if ($mDelId) {
                $oDesigner = $oEtt->getDesigner('delete');
                /* @var $oDesigner \fan\core\service\entity\designer\delete */
                $sQuery    = $oDesigner->setDeleteByParam($this->getId(false, true, true))->assemble();
                $aAdjParam = $oDesigner->getAdjustedParam();
                $oConnect = $oEtt->getConnection();
                $oConnect->execute($sQuery, $aAdjParam);
                if ($oConnect->isError()) {
                    return false;
                }

                $this->_resetProperty(false);
                $this->_runAfterDelete($mDelId);
                //\fan\project\service\cache::instance()->clearCacheByEntity($this);
                return true;
            } else {
                throw new fatalException($this->getEntity(), 'Can\'t get source ID for delete row.');
            }
        }
        return false;
    } // function delete

    /**
     */
    public function getId($bAllowException = true, $bUseSourceValue = false, $bAlwaysArray = false)
    {
        $mIdKey = $this->getEntity()->description->getPrimeryKey();
        if (is_array($mIdKey)) {
            $aResult = array();
            foreach ($mIdKey as $k) {
                $aResult[$k] = $bUseSourceValue && isset($this->aSrcData[$k]) ? $this->aSrcData[$k] : $this->get($k, null, $bAllowException);
            }
            return $aResult;
        }
        $mResult = $bUseSourceValue && isset($this->aSrcData[$mIdKey]) ? $this->aSrcData[$mIdKey] : $this->_getFieldValue($mIdKey, null, $bAllowException);
        return $bAlwaysArray ? array($mIdKey => $mResult) : $mResult;
    } // function getId

    /**
     * Set Id
     * @param mixed $mIdVal
     * @throws fatalException
     */
    public function setId($mIdVal)
    {
        $mIdKey = $this->getEntity()->description->getPrimeryKey();
        if(is_array($mIdKey)) {
            foreach($mIdVal as $k => $v) {
                if(!in_array($k, $mIdKey)) {
                    throw new fatalException($this, 'Incorrect id name (as array)!');
                }

                $this->_setFieldValue($k, $v);
            }
        } else {
            $this->_setFieldValue($mIdKey, $mIdVal);
        }
    } // function setId

    /**
     * Get instance of Entity
     * @return \fan\core\base\model\entity
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity
    /**
     * Get instance of Rowset
     * @return \fan\core\base\model\rowset
     */
    public function getRowset()
    {
        return $this->oRowset;
    } // function getRowset

    /**
     * Chek is Data loaded
     * @return boolean true - if data load succesfuly
     */
    public function checkIsLoad()
    {
        return $this->bIsDataLoad;
    } // function checkIsLoad

    /**
     * Gets All source Fields
     * @return array Fields
     */
    public function getSrcFields()
    {
        return $this->aSrcData;
    } // function getSrcFields

    /**
     * Gets Changed Elements
     * @return array Fields
     */
    public function getChangedElm()
    {
        return $this->aChanged;
    } // function getChangedElm

    /**
     * Get default values for Insert operation
     */
    public function getDefaultValue()
    {
        $aDefaultValue = array();
        foreach ($this->_getFullFieldsInfo() as $k => $v) {
            if (!$v['auto_increment']) {
                $aDefaultValue[$k] = $v['default'];
            }
        }
        return $aDefaultValue;
    } // function getDefaultValue

    /**
     */
    public function getDebugInfo()
    {
        return array(
            'entity_name' => $this->getEntity()->getName(true),
            'data'        => $this->getFields(),
            'src_data'    => $this->aSrcData,
            'changed'     => $this->aChanged,
            'flags'       => array(
                'is_load'    => $this->bIsDataLoad,
                'show_error' => $this->bShowError,
                'local'      => $this->sCurrentLocal
            ),
            'connection' => $this->getEntity()->getConnectionName(),
        );
    } // function getDebugInfo

    /**
     * Set Show Error
     * @param boolean $bShowError
     * @return \fan\core\base\model\row
     */
    public function setShowError($bShowError)
    {
        $this->bShowError = !empty($bShowError);
        return $this;
    } // function setShowError

    // ======== Private/Protected methods ======== \\
    /**
     * Fix Loaded Data
     * @param array $aData
     * @return boolean
     */
    protected function _fixLoadedData(&$aData)
    {
        if (empty($aData)) {
            $this->_resetProperty();
        } else {
            $this->bIsDataLoad = true;
            $this->aSrcData    =  $aData;
            $this->aData       =& $aData;
            $this->aChanged    = array();
        }
        return $this->bIsDataLoad;
    } // function _fixLoadedData

    /**
     * Insert DB-row
     * @return \fan\core\base\model\row
     */
    protected function _insertRow()
    {
        if (empty($this->aChanged)) {
            return $this;
        }

        $this->_setDefault();
        if (empty($this->aData)) {
            return $this;
        }

        $oEtt      = $this->getEntity();
        $oConnect  = $oEtt->getConnection();
        $oDesigner = $oEtt->getDesigner('insert');
        /* @var $oDesigner \fan\core\service\entity\designer\insert */
        $sQuery    = $oDesigner->setInsertByParam($this->aData)->assemble();
        $aAdjParam = $oDesigner->getAdjustedParam();
        $oConnect->execute($sQuery, $aAdjParam);

        $sErrMsg = $oConnect->getErrorMessage();
        if (!$sErrMsg) {
            foreach ($this->_getFullFieldsInfo() as $k => $v) {
                if ($v['auto_increment'] && empty($this->aData[$k])) {
                    $this->aData[$k] = $oConnect->getInsertId();
                } elseif (!isset($this->aData[$k])) {
                    $this->aData[$k] = null;
                }
            }

            $this->bIsDataLoad = true;
            $this->aChanged    = array();

            //ToDo: if ($this->bCacheIt) {}
        } elseif ($this->bShowError) {
            \fan\project\service\error::instance()->logErrorMessage($sErrMsg, 'Data isn\'t inserted.', 'Entity name: ' . $oEtt->getName(true) . "\n\n" . $sQuery . "\nData: " . var_export($aAdjParam, true));
        }
        return $this;
    } // function _insertRow

    /**
     * Update DB-row
     * @return \fan\core\base\model\row
     * @throws fatalException
     */
    protected function _updateRow()
    {
        if (empty($this->aChanged)) {
            return $this;
        } // check rows

        $oEtt     = $this->getEntity();
        $aIdValue = $this->getId(false, true);
        if (empty($aIdValue)) {
            throw new fatalException($oEtt, 'Update impossible. ID isn\'t set!');
        }

        $oConnect  = $oEtt->getConnection();
        $oDesigner = $oEtt->getDesigner('update');
        /* @var $oDesigner \fan\core\service\entity\designer\update */
        $sQuery    = $oDesigner->setUpdateByParam($this->aChanged, $this->getId(false, true, true))->assemble();
        $aAdjParam = $oDesigner->getAdjustedParam();
        $oConnect->execute($sQuery, $aAdjParam);

        $sErrMsg = $oConnect->getErrorMessage();
        if (!$sErrMsg) {
            $this->aChanged = array();
        } elseif ($this->bShowError) {
            \fan\project\service\error::instance()->logErrorMessage($sErrMsg, 'Data isn\'t updated.', 'Entity name: ' . $oEtt->getName(true) . "\n\n" . $sQuery . "\nData: " . var_export($aAdjParam, true));
        }
        return $this;
    } // function _updateRow

    /**
     * Get field-info
     * @param string $sFieldName
     * @param boolean $bAllowException
     * @return array
     * @throws fatalException
     */
    protected function _getFieldInfo($sFieldName, $bAllowException = true, $bForse = false)
    {
        $aFieldInfo = $this->_getFullFieldsInfo($bForse);
        if (!isset($aFieldInfo[$sFieldName])) {
            $sErrorMessage  = 'Incorrect field name "' . $sFieldName . '" for ';
            $sErrorMessage .= empty($this->oEntity) ? 'unknown table.' : 'table "' . $this->oEntity->getTableName() . '".';
            if ($bAllowException) {
                throw new fatalException($this->getEntity(), $sErrorMessage);
            }
            trigger_error($sErrorMessage, E_USER_NOTICE);
            return null;
        }
        return $aFieldInfo[$sFieldName];
    } // function _getFieldInfo

    /**
     * Get Full Info about Fields
     * @return array
     */
    protected function _getFullFieldsInfo($bForse = false)
    {
        if (empty($this->aFieldInfo) || $bForse) {
            $this->aFieldInfo = $this->getEntity()->description->getFields($bForse);
        }
        return $this->aFieldInfo;
    } // function _getFullFieldsInfo

    /**
     * Is String Type field
     * @param string $sType
     * @return boolean
     */
    protected function _isStringType($sType)
    {
        return in_array(strtolower($sType), array('char', 'varchar', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob'));
    } // function _isStringType

    /**
     * Is Number Type field
     * @param string $sType
     * @return boolean
     */
    protected function _isNumberType($sType)
    {
        return in_array(strtolower($sType), array('tinyint', 'bit', 'bool', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double', 'decimal', 'dec'));
    } // function _isNumberType


    /**
     * Get value of data
     * @param number $sFieldName
     * @return mixed
     */
    protected function _getFieldValue($sFieldName, $mDefaultVal = null, $bAllowException = true)
    {
        if ($this->bInitIdOnly || $bAllowException) {
            $this->_checkGetWrongValue($sFieldName);
        }
        return isset($this->aData[$sFieldName]) ? $this->aData[$sFieldName] : $mDefaultVal;
    } // function _getFieldValue

    /**
     * Set value of data
     * @param array|entity $mValue
     * @return \fan\core\base\data
     */
    protected function _setFieldValue($sFieldName, $mValue, $bAllowException = true)
    {
        $aFieldInfo = $this->_getFieldInfo($sFieldName, $bAllowException);
        if ($aFieldInfo) {
            $bIsNumber = $this->_isNumberType($aFieldInfo['type']);
            $bIsString = $this->_isStringType($aFieldInfo['type']);

            if (is_null($mValue) && (!$aFieldInfo['null'] && !$aFieldInfo['auto_increment'])) {
                $mValue = $bIsNumber ? 0 : ($bIsString ? '' : null);
            } elseif ($bIsString) {
                if (is_array($mValue)) {
                    service('error')->logErrorMessage('Value of field "' . $sFieldName . '" can\'t be set as Array', 'Error set value of row', '', true, false);
                    $mValue = '';
                } else {
                    $mValue = (string)$mValue;
                }
                if (isset($aFieldInfo['length'])) {
                    $bIsUtf8 = $aFieldInfo['charset'] == 'utf8';
                    if (call_user_func($bIsUtf8 ? 'mb_strlen' : 'strlen', $mValue) > $aFieldInfo['length']) {
                        $mValue = call_user_func($bIsUtf8 ? 'mb_substr' : 'substr', $mValue, 0, $aFieldInfo['length']);
                        //ToDo: Notify about truncated data, by Config parameter
                    }
                }
            }

            if (!array_key_exists($sFieldName, $this->aData) || $this->aData[$sFieldName] != $mValue) {
                $this->aChanged[$sFieldName] = $mValue;
            }
            $this->aData[$sFieldName] = $mValue;
        }
        return $this;
    } // function _setFieldValue
    /**
     * Set default values for Insert operation
     */
    protected function _setDefault()
    {
        foreach ($this->getDefaultValue() as $k => $v) {
            // ToDo: Set default value for enum if it is not null
            if((!isset($this->aData[$k]) || is_null($this->aData[$k])) && !is_null($v)) {
                $this->aData[$k] = $v;
            }
        }
        return $this;
    } // function _setDefault

    /**
     * Set all Property as new Row
     * @param type $bFull
     * @return \fan\core\base\model\row
     */
    protected function _resetProperty($bFull = true)
    {
        $this->bIsDataLoad = false;
        if($bFull) {
            $this->aSrcData = array();
        }
        $this->aData    = array();
        $this->aChanged = array();

        return $this;
    } // function _resetProperty

    /**
     * Get Current Local field suffix
     * @return string
     */
    protected function _getCurrentLocal()
    {
        if (!$this->sCurrentLocal) {
            $this->sCurrentLocal = '_' . \fan\project\service\locale::instance()->getLanguage();
        }
        return $this->sCurrentLocal;
    } // function _getCurrentLocal


    /**
     * Get Default Local field suffix
     * @return string
     */
    protected function _getDefaultLocal()
    {
        if (!$this->sDefaultLocal) {
            $this->sDefaultLocal = '_' . \fan\project\service\locale::instance()->getDefaultLanguage();
        }
        return $this->sDefaultLocal;
    } // function _getDefaultLocal

    /**
     * Check Get Wrong Value
     * @param string $sFieldName
     * @throws fatalException
     */
    protected function _checkGetWrongValue($sFieldName)
    {
        if ($this->bInitIdOnly && !array_key_exists($sFieldName, $this->aChanged)) {
            throw new fatalException($this->getEntity(), 'This instance has been created for UPDATE DB-row. You can\'t read "' . $sFieldName . '" because it contains wrong value now! ');
        }
        if (!array_key_exists($sFieldName, $this->aData)) {
            throw new fatalException($this->getEntity(), 'Call for unset field "' . $sFieldName . '"! ' . "\n Exist fields:" . var_export($this->aData, true));
        }
    } // function _checkGetWrongValue

    /**
     * Restore Current object Properties
     * @return \fan\core\base\model\row
     */
    protected function _restoreProperties()
    {
        $this->bShowError = $this->getConfig('SHOW_ERROR', $this->bShowError);
        return $this;
    } // function _restoreProperties

    /**
     * Convert value to string
     * @param mixed $mVal
     * @return string
     */
    protected function _convToString($mVal)
    {
        switch (gettype($mVal)) {
        case 'NULL':
            return 'NULL';
        case 'string':
            return '"' . $mVal . '"';
        case 'boolean':
            return $mVal ? 'true' : 'false';
        case 'object':
            return 'object ' . get_class($mVal);
        }
        return (string)$mVal;
    } // function _restoreProperties

    // ======== The magic methods ======== \\
    public function __set($sFieldName, $mValue)
    {
        $this->set($sFieldName, $mValue);
    }

    public function __get($sFieldName)
    {
        return $this->get($sFieldName);
    }
    /**
     * Call to unset entity method
     * @param string $sMethod method name
     * @param array $aArgs arguments
     * @return mixed Value return by engine
     */
    public function __call($sMethod, $aArgs)
    {
        if(substr($sMethod, 0, 4) == 'set_') {
            return $this->set(substr($sMethod, 4), isset($aArgs[0]) ? $aArgs[0] : null);
        } elseif (substr($sMethod, 0, 4) == 'get_') {
            return $this->get(substr($sMethod, 4), isset($aArgs[0]) ? $aArgs[0] : null, isset($aArgs[1]) ? $aArgs[1] : true);
        } else {
            throw new fatalException($this->getEntity(), 'Incorrect call of entity method: "' . $sMethod . '"');
        }
    } // function __call

    public function __toString() {
        $sRet = '';
        foreach ($this->aData as $k => $v) {
            $sRet .= empty($sRet) ? '' : ', ';
            $sRet .= $k . ' => ' . $this->_convToString($v);
        }
        return '(' . $sRet . ')';
    }

    // ======== Required Interface methods ======== \\
    public function offsetSet($sFieldName, $mValue)
    {
        $this->set($sFieldName, $mValue);
    }

    public function offsetExists($sFieldName)
    {
        return isset($this->aData[$sFieldName]);
    }

    public function offsetUnset($sFieldName)
    {
        $this->set($sFieldName, null);
    }

    public function offsetGet($sFieldName)
    {
        return $this->get($sFieldName);
    }

    public function serialize()
    {
        return serialize(array(
            'mainParam'  => $this->getEntity()->getMainParam(),
            'srcData'    => $this->aSrcData,
            'data'       => $this->aData,
            'changed'    => $this->aChanged,
            'isDataLoad' => $this->bIsDataLoad,
            'initIdOnly' => $this->bInitIdOnly,
        ));
    }

    public function unserialize($sData)
    {
        $aData = unserialize($sData);

        $this->aSrcData    = $aData['srcData'];
        $this->aData       = $aData['data'];
        $this->aChanged    = $aData['changed'];
        $this->bIsDataLoad = $aData['isDataLoad'];
        $this->bInitIdOnly = $aData['initIdOnly'];

        $aParam = $aData['mainParam'];
        $oServ  = \fan\project\service\entity::instance($aParam['collection']);
        $this->oEntity = empty($aParam['name']) ?
                $oServ->getAnonymous($aParam['class'], $aParam['param']) :
                $oServ->get($aParam['name'], $aParam['param']);
        $this->oEntity->setConnectionName($aParam['connection']['name'])->setConnectionKey($aParam['connection']['key']);

        $this->_restoreProperties();
    }
} // class \fan\core\base\model\row
?>