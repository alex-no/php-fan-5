<?php namespace fan\core\base;
/**
 * Base abstract service
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
 * @abstract
 */
abstract class transfer extends \Exception
{
    /**
     * @var string Transfer Type
     */
    protected $sTransferType;
    /**
     * @var string Applicatin name
     */
    protected $sNewUri;
    /**
     * @var string New Query String
     */
    protected $sNewQueryString;

    /**
     * Transfer's constructor
     * @param string $sNewUri New Transfer's URL
     * @param string $sNewQueryString New Query String
     * @param string $sDbOper Database Operation (commit, rollback)
     */
    public function __construct($sNewUri, $sNewQueryString = null, $sDbOper = null)
    {
        $this->sNewUri = $sNewUri;
        $this->sNewQueryString = $sNewQueryString;
        if ($sDbOper) {
            \fan\project\service\database::fixAll($sDbOper, false);
        }
        parent::__construct($this->sTransferType, E_USER_NOTICE);
    }

    /**
     * Get Transfer Type
     * @return string
     */
    public function getTransferType()
    {
        return $this->sTransferType;
    } // function getTransferType

    /**
     * Get Request
     * @return string
     */
    public function getRequest()
    {
        $sNewUri      = $this->getNewUri();
        $sQueryString = $this->getNewQueryString();
        if (empty($sQueryString) || $sQueryString == '?') {
            return  $sNewUri;
        }
        $sMainUri = strstr($sNewUri, '?', true);
        return (empty($sMainUri) ? $sNewUri : $sMainUri) . '?' . ltrim($sQueryString, '?');
    } // function getRequest

    /**
     * Get Host
     * @return string
     */
    public function getHost()
    {
        return null;
    } // function getHost

    /**
     * Is Shift Current matcher stack
     * @return boolean
     */
    public function isShiftCurrent()
    {
        return $this->getTransferType() != 'sham';
    } // function isShiftCurrent

    /**
     * Get New Url
     * @return string
     */
    public function getNewUri()
    {
        return $this->sNewUri;
    } // function getNewUri

    /**
     * Get Public error-message
     * @return string
     */
    public function getNewQueryString()
    {
        return $this->sNewQueryString;
    } // function getNewQueryString

} // class \fan\core\base\transfer
?>