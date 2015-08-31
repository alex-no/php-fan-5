<?php namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Description of rowset
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
class rowset extends \fan\core\base\data
{
    /**
     * Class of DB-table
     * @var fan\core\entity\table
     */
    protected $oEntity = null;

    /**
     * TAble describer
     * @param \fan\core\base\model\entity $oEntity
     */
    public function __construct(\fan\core\base\model\entity $oEntity, &$aData)
    {
        $this->oEntity = $oEntity;

        $sRowClass = $oEntity->getRowClassName();
        foreach ($aData as $k => &$v) {
            $this->set($k, new $sRowClass($oEntity, $v, $this));
        }

        $this->_setSetter($this);
        $this->bMultiLevel = false;
    } // function __construct

    // ======== Main Interface methods ======== \\

    /**
     * Convert this object to array
     *   If $bRecursive is true - entity also converted to Array
     * @param boolean $bRecursive
     * @return array
     */
    public function toArray($bRecursive = false)
    {
        if (!$bRecursive) {
            return $this->aData;
        }
        $aRet = array();
        foreach ($this->aData as $k => $v) {
            $aRet[$k] = $v->toArray();
        }
        return $aRet;
    } // function toArray

    /**
     * Get array of Row-object, where ID as key
     * @return \fan\core\base\model\entity[]
     * @throws fatalException
     */
    public function getRowsById()
    {
        if (!$this->_isScalarId()) {
            throw new fatalException($this->getEntity(), 'Method "getRowsById" allowed only for Scalar Id!');
        }
        $aRet = array();
        foreach ($this->aData as $v) {
            $aRet[$v->getId()] = $v;
        }
        return $aRet;
    } // function getRowsById

    /**
     * Get array of data, where ID as key
     * @param mixed $mFields Array/string of Fields list
     * @param boolean $bExcludeId Exclude ID-field
     * @return array
     * @throws fatalException
     */
    public function getArrayAssoc($mFields = array(), $bExcludeId = true, $sKeyPrefix = null)
    {
        if (!$this->_isScalarId()) {
            throw new fatalException($this->getEntity(), 'Method "getArrayAssoc" allowed only for Scalar Id!');
        }
        if (is_string($mFields)) {
            if ($mFields == '*') {
                $mFields = array();
            } else {
                $mFields = explode(',', $mFields);
                $mFields = array_map('trim', $mFields);
            }
        }

        $aRet = array();
        foreach ($this->aData as $v) {
            $aTmp = $v->toArray();
            if (!empty($mFields)) {
                foreach ($aTmp as $k1 => $v1) {
                    if (!in_array($k1, $mFields)) {
                        unset($aTmp[$k1]);
                    }
                }
                foreach ($mFields as $k2) {
                    if (!isset($aTmp[$k2])) {
                        $aTmp[$k2] = null;
                    }
                }
            }
            if ($bExcludeId) {
                unset($aTmp[$v->getId()]);
            }
            $k = $v->getId();
            if (!is_null($sKeyPrefix)) {
                $k = $sKeyPrefix . $k;
            }
            $aRet[$k] = $aTmp;
        }
        return $aRet;
    } // function getArrayAssoc

    /**
     * Get Column-data as one-dimensional array
     *   If $bIdKey is true - Id is used as key
     * @param type $sColumnName
     * @param type $bIdAsKey
     * @return type
     */
    public function getColumn($sColumnName, $bIdAsKey = true)
    {
        $bIdAsKey = $bIdAsKey && $this->_isScalarId();
        $aRet = array();
        foreach ($this->aData as $k => $v) {
            $aRet[$bIdAsKey ? $v->getId() : $k] = $v[$sColumnName];
        }
        return $aRet;
    } // function getColumn

    /**
     * Get one-dimensional array where $sKeyField as key and $sValField as value
     * @param mixed $sKeyField
     * @param mixed $sValField
     * @return array
     */
    public function getArrayHash($sKeyField, $sValField)
    {
        $aRet = array();
        foreach ($this->aData as $v) {
            $aRet[$v[$sKeyField]] = $v[$sValField];
        }
        return $aRet;
    } // function getArrayHash

    /**
     * Get instance of Entity
     * @return type
     */
    public function getEntity()
    {
        return $this->oEntity;
    } // function getEntity

    // ======== Private/Protected methods ======== \\

    /**
     * Check Id is Scalar-type
     * @return boolean
     */
    protected function _isScalarId()
    {
        return !is_array($this->getEntity()->description->getPrimeryKey());
    }

    // ======== Required Interface methods ======== \\

    public function serialize() {
        if (empty($this->aData)) {
            trigger_error('An empty rowset can\'t be serialized!', E_USER_ERROR);
            return serialize(null);
        }
        return parent::serialize();
    }
    public function unserialize($sRecover) {
        if (is_null(unserialize($sRecover))) {
            throw new fatalException($this->getEntity(), 'An empty rowset can\'t be unserialized!');
        }
        parent::unserialize($sRecover);
        $this->oEntity = reset($this->aData)->getEntity();
    }

} // class \fan\core\base\model\rowset
?>