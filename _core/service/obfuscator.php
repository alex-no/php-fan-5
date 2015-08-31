<?php namespace fan\core\service;
use fan\core\exception\service\fatal as fatalException;
use fan\core\exception\processing\control as controlException;
/**
 * Paiment-maker service
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
 * @author: Alex Nosov (alex@4n.com.ua)
 */
class obfuscator extends \fan\core\base\service\multi
{
    /**
     * @var array Service's Instances
     */
    private static $aInstances = array();
    /**
     * List of Engines by TA Types
     * @var array
     */
    private $aFileType = array(
        'css',
        'js',
    );
    /**
     * Current Type of File (css or js)
     * @var numeric
     */
    protected $sType = null;

    /**
     * Service's constructor
     * @param string $sType
     * @throws controlException
     */
    protected function __construct($sType)
    {
        if (in_array($sType, $this->aFileType)) {// As Paysys-name
            $this->sType = $sType;
        } else {
            throw new controlException(0, 'Unknown name of paySystem "' . $mPaySysKey . '"', 3008);
        }

        parent::__construct(true);

        //$this->oEngine = $this->_getEngine($this->sType);
    } // function __construct

    // ======== Static methods ======== \\

    /**
     * Get Service's instance of current service by Name of Provider
     * @param string $sType Name of PaySystem
     * @return \fan\core\service\obfuscator
     */
    public static function instance($sType)
    {
        if (strstr($sType, ':')) {
            $sType = strstr($sType, ':', true);
        }
        if (!isset(self::$aInstances[$sType])) {
            new self($sType);
        }
        return self::$aInstances[$sType];
    } // function instance

    // ======== Main Interface methods ======== \\

    /**
     * Get Paiment Name
     * @return string
     */
    public function getPayName()
    {
        return $this->sPaySystem;
    } // function getPayName

    /**
     * Get transaction type
     * @return numeric
     */
    public function getPayType()
    {
        return $this->nPayType;
    } // function getPayType

    /**
     * Get is debug-mode
     * @return string
     */
    public function isDebug()
    {
        return service('pay_wrapper')->isDebug();
    } // function isDebug

    // ======== Private/Protected methods ======== \\

    /**
     * Save service's Instance
     * @return \fan\core\base\service
     */
    protected function _saveInstance()
    {
        self::$aInstances[$this->sPaySystem] = $this;
        self::$aInstances[$this->nPayType] = $this;
        return $this;
    } // function _saveInstance

    /**
     * Get delegate class
     * @param string $sClass
     * @return object
     * @throws \fan\core\exception\service\fatal
     */
    protected function _getDelegate($sClass)
    {
        if ($sClass != 'paySystem') {
            throw new fatalException($this, 'Delegate service class "' . $sClass . '" isn\'t found!');
        }
        return $this->oEngine;
    } // function _getDelegate
} // class \fan\core\service\obfuscator
?>