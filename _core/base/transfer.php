<?php namespace core\base;
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
 * @version of file: 05.007 (23.02.2014)
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
    protected $sNewUrn;
    /**
     * @var string New Query String
     */
    protected $sNewQueryString;

    /**
     * Transfer's constructor
     * @param string $sNewUrn New Transfer's URL
     * @param string $sNewQueryString New Query String
     * @param string $sDbOper Database Operation (commit, rollback)
     */
    public function __construct($sNewUrn, $sNewQueryString = null, $sDbOper = null)
    {
        $this->sNewUrn = $sNewUrn;
        $this->sNewQueryString = $sNewQueryString;
        if ($sDbOper) {
            \project\service\database::fixAll($sDbOper, false);
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
        $sQueryString = $this->getNewQueryString();
        return $this->getNewUrn() . (empty($sQueryString) ? '' : $sQueryString);
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
    public function getNewUrn()
    {
        return $this->sNewUrn;
    } // function getNewUrn

    /**
     * Get Public error-message
     * @return string
     */
    public function getNewQueryString()
    {
        return $this->sNewQueryString;
    } // function getNewQueryString

} // class \core\base\transfer
?>