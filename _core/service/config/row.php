<?php namespace core\service\config;
/**
 * Meta Data Row
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
 * @version of file: 05.001 (29.09.2011)
 */
class row extends \core\base\data
{
    /**
     * Saved source data
     * @var array
     */
    protected $aSrcData = array();

    /**
     * Facade of service
     * @var core\base\service
     */
    protected $oFacade = null;

    /**
     * Services - owners of config
     * @var array
     */
    protected $aOwners = array();

    /**
     * Rooy Key of element
     * @var string
     */
    protected $sRootKey = null;

    /**
     * Constructor of Config-data
     * @param array $aData
     * @param string $sKey
     * @param core\service\config\row $oSuperior
     */
    public function __construct($aData, $sKey = null, $oSuperior = null)
    {
        parent::__construct($aData, $sKey, $oSuperior);

        $this->aSrcData = $this->aData;
        //$this->aErrMsg[91] = 'Facade isn\'t set';
    } // function __construct

    // ======== Main Interface methods ======== \\
    /**
     * Set Facade
     * @param \core\base\service $oFacade
     */
    public function setFacade(\core\base\service $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
            $this->_setSetter($oFacade);
        }
        foreach ($this->_getSubData() as $v) {
            $v->setFacade($oFacade);
        }
        return $this;
    } // function setFacade

    /**
     * Get Root Key
     */
    public function getRootKey()
    {
        if (is_null($this->sRootKey)) {
            if (empty($this->oSuperior)) {
                $this->sRootKey = '';
            } else {
                $sRootKey = $this->oSuperior->getRootKey();
                $this->sRootKey = empty($sRootKey) ? $this->sKey : $sRootKey;
            }
        }
        return $this->sRootKey;
    } // function getRootKey

    /**
     * Set Service - Owner of Config
     * @param \core\base\service $oService
     * @return \core\service\config\row
     */
    public function setServiceOwner(\core\base\service $oService)
    {
        $sName = get_class_name($oService);
        if ($sName == $this->getRootKey()) {
            $this->_setSetter($oService);
            $this->aOwners[] = $oService;
            foreach ($this->_getSubData() as $v) {
                $v->setServiceOwner($oService);
            }
        }
        return $this;
    } // function setServiceOwner
    /**
     * Set Plain Controller - Owner of Config
     * @param object $oCtrl
     * @param string $sName
     * @return \core\service\config\row
     */
    public function setPlainOwner($oCtrl, $sName)
    {
        if ($sName == $this->getRootKey()) {
            $this->_setSetter($oCtrl);
            $this->aOwners[] = $oCtrl;
            foreach ($this->_getSubData() as $v) {
                $v->setPlainOwner($oCtrl);
            }
        }
        return $this;
    } // function setPlainOwner

    /**
     * Set Entity - Owner of Config
     * @param \core\base\model\entity $oEntity
     * @param string $sName
     * @return \core\service\config\row
     */
    public function setEntityOwner(\core\base\model\entity $oEntity, $sName)
    {
        if ($sName == $this->getRootKey()) {
            $this->_setSetter($oEntity);
            $this->aOwners[] = $oEntity;
            foreach ($this->_getSubData() as $v) {
                $v->setEntityOwner($oEntity);
            }
        }
        return $this;
    } // function setEntityOwner
    /**
     * Get array of Owners
     * @return array
     */
    public function getOwners()
    {
        return $this->aOwners;
    } // function getOwners

    /**
     * Get array of Owners
     * @return array
     */
    public function getSources()
    {
        $oClone = clone $this;
        $oClone->reset();
        return $oClone->toArray();
    } // function getSources

    /**
     * Reset Config data
     * @return \core\service\config\row
     */
    public function reset()
    {
        if ($this->_checkSetter()) {
            $this->aData = $this->aSrcData;
            foreach ($this->_getSubData() as $v) {
                $v->reset();
            }
        }
        return $this;
    } // function reset

    /**
     * Merge data
     * @param array|\core\service\config\row $aData
     * @param boolean $bPriority
     * @return \core\service\config\row
     */
    public function mergeData($aData, $bPriority = true)
    {
        if (is_object($aData) && $aData instanceof \core\service\config\row) {
            $aData = $aData->toArray();
        }
        if ($this->_checkSetter() && is_array($aData)) {
            foreach ($aData as $k => $v) {
                if ($bPriority || !isset($this->aData[$k])) {
                    $this->set($k, $v);
                }
            }
        }
        return $this;
    } // function mergeData

    // ======== Private/Protected methods ======== \\
    /**
     * Convert Array to another structure (usually instance of this class)
     * Methd need to redefine in children classes if it use another parameter of constructor
     * @param string $sKey
     * @param array $aValue
     * @return mixed
     */
    protected function _makeSubData($sKey, $aValue)
    {
        $sClass = get_class($this);
        $oSubData = new $sClass($aValue, $sKey, $this);
        if (!empty($this->oFacade)) {
            $oSubData->setFacade($this->oFacade);
        }
        return $oSubData;
    } // function _makeSubData

    // ======== The magic methods ======== \\
    /**
     * Clone config
     */
    function __clone() {
        $this->reset();
    }

    public function __unset($sKey)
    {
        // Todo: Do this "throw" only if it is enabled in config
        throw new \project\exception\service\fatal($this->oFacade, 'You can\'t unset data for key "' . $sKey . '".');
    }

    // ======== Required Interface methods ======== \\

    public function serialize() {
        return serialize(array(
            'parent'  => parent::serialize(),
            'srcData' => $this->aSrcData,
            'rootKey' => $this->sRootKey,
        ));
    }
    public function unserialize($sRecover) {
        $aRecover = unserialize($sRecover);

        parent::unserialize($aRecover['parent']);

        $this->aSrcData = $aRecover['srcData'];
        $this->sRootKey = $aRecover['rootKey'];
    }

} // class \core\service\config\row
?>