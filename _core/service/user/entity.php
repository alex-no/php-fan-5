<?php namespace fan\core\service\user;
use fan\project\exception\service\fatal as fatalException;
/**
 * User-data engine by data from entity
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class entity extends base
{
    /**
     * DB row data
     * @var \fan\core\base\model\row
     */
    protected $oRow;

    /**
     * Allowed Get/Set Entity-Methods
     * @var array
     */
    protected $aMapping = array();

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Convert text of password to text of hash
     * @param string $sPassword
     * @return string
     */
    public function makePasswordHash($sPassword)
    {
        $sLogin = array_val($this->mData, 'login', $this->mIdentifyer);
        return $sLogin ? md5($sLogin . $sPassword . $this->oConfig->get('ENGINE_KEY')) : '';
    } // function makePasswordHash

    // ======== Private/Protected methods ======== \\

    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _loadData()
    {
        $oConf = $this->oConfig;
        $this->oRow = null;
        foreach ($oConf->get('IDENTIFYERS') as $v) {
            $oRow = ge($oConf->get('ENGINE_KEY'))->getRowByParam(array($v => $this->mIdentifyer));
            if ($oRow->checkIsLoad()) {
                $this->oRow  = $oRow;
                $this->mData = $this->_getEntityData();
                return true;
            }
        }
        return false;
    } // function _loadData

    /**
     * Save User Data and return TRUE if success
     * @return boolean
     */
    protected function _saveData()
    {
        $oRow = $this->_getRow();
        if (!empty($oRow) && $this->isChanged()) {
            $aMethods = $this->_getMethodList('Setting');
            if (!empty($aMethods)) {
                foreach ($this->aChanged as $k => $v) {
                    if (!empty($aMethods[$k])) {
                        $sMethod = $aMethods[$k];
                        $oRow->$sMethod($v);
                    }
                }
                $oRow->save();
            }
            return true;
        }
        return false;
    } // function _saveData

    /**
     * Validate User Data before saving
     * @return boolean
     */
    protected function _validateForSave()
    {
        $oRow  = $this->_getRow();
        return !empty($oRow);
    } // function _validateForSave

    /**
     * Get Data by Entity
     * @return array
     */
    protected function _getEntityData()
    {
        $aMethods = $this->_getMethodList('Getting');
        if (!empty($aMethods)) {
            $aRequired = array('id' => 0, 'password' => 0, 'login' => 0, 'roles' => 0);
            if (count(array_intersect_key($aMethods, $aRequired)) < 4) {
                throw new fatalException($this->oFacade, 'Required keys "' . implode('", "', aray_keys($aRequired)) . '" are not get by method "getGettingMap".');
            }
        }

        $aData = array();
        $oRow  = $this->_getRow();
        if (empty($aMethods)) {
            foreach ($this->_getKeyList() as $k) {
                $v = 'get_' . $v;
                $aData[$k] = $oRow->$v(null, false);
            }
        } else {
            foreach ($aMethods as $k => $v) {
                $aData[$k] = $oRow->$v();
            }
        }

        return $aData;
    } // function _getEntityData

    /**
     * Get List of Method
     * @param string $sType
     * @return array|null
     * @throws fatalException
     */
    protected function _getMethodList($sType)
    {
        if (!in_array($sType, array('Getting', 'Setting'))) {
            throw new fatalException($this->oFacade, 'Incorrect type of mapping "' . $sType . '".');
        }

        while (!isset($this->aMapping[$sType])) {
            $oRow = $this->_getRow();
            if (empty($oRow)) {
                return null;
            }

            $sMethod = 'get' . $sType . 'Map';
            $aKeys   = array_flip($this->_getKeyList());
            if (method_exists($oRow, $sMethod)) {
                $aMap = $oRow->$sMethod($sType);
            } elseif ($sType == 'Getting') {
                $aMap = null;
            } else {
                $sErr  = 'Method for mapping User-data "' . get_class_alt($oRow) . '::' . $sMethod . '()" isn\'t set.' . "\n";
                $sErr .= 'Keys: ("' . implode('", "', array_keys($aKeys)) . '").';
                throw new fatalException($this->oFacade, $sErr);
            }

            $this->aMapping[$sType] = empty($aMap) ? array() : array_intersect_key($aMap, $aKeys);
        }

        return $this->aMapping[$sType];
    } // function _getMethodList

    /**
     * Get Row
     * @return \fan\core\base\model\row|null
     */
    protected function _getRow()
    {
        if (empty($this->oRow)) {
            $sEttKey = $this->oConfig->get('ENGINE_KEY');
            if ($this->bIsNew) {
                $this->oRow = gr($sEttKey);
            } elseif (!empty($this->mData['id'])) {
                $this->oRow = gr($sEttKey, $this->mData['id']);
            } else {
                return null;
            }
        } elseif (!$this->oRow->checkIsLoad() && !$this->bIsNew) {
            return null;
        }
        return $this->oRow;
    } // function _getRow

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\service\user\entity
?>