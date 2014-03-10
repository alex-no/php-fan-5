<?php namespace fan\core\service;
/**
 * Description of matcher
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
class matcher extends \fan\core\base\service\single
{
    /**
     * @var \fan\core\service\matcher\stack Stack of requested URI
     */
    protected $oStack;

    // ============= Init Data ============= \\
    /**
     * Matcher constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        $this->oStack = $this->_getEngine('stack');
        parent::__construct($bAllowIni);
    } // function __construct

    /**
     * Set Uniform Resource Indicator
     * @param string $sRequest
     * @param string $sHost
     * @return \fan\core\service\matcher
     */
    public function setUri($sRequest, $sHost = null, $bShiftCurrent = true)
    {
        $this->oStack->setNewItem($sRequest, $sHost, $bShiftCurrent);
        $this->_broadcastMessage('setNewUri', $this);
        return $this;
    } // function setUri

    /**
     * Set Command Line Interface
     * @param type $sFile
     * @param type $sPath
     * @return \fan\core\service\matcher
     */
    public function setCli($sFile, $sPath = null)
    {
        $this->oStack->setNewItem($sFile, $sPath, false);
        $this->_broadcastMessage('setNewUri', $this);
        return $this;
    } // function setCli

    // ============= Get Current/Last indexes ============= \\
    /**
     * Get Last stack Index
     * @return integer
     */
    public function getLastIndex()
    {
        return $this->oStack->getLastIndex();
    } // function getUriIndex

    /**
     * Get Current stack Index
     * @return integer
     */
    public function getCurrentIndex()
    {
        return $this->oStack->getCurrentIndex();
    } // function getCurrentIndex

    // ============= Get Common Data ============= \\
    /**
     * Get Stack of Transfers
     * @return \fan\core\service\matcher\stack
     */
    public function getStack()
    {
        return $this->oStack;
    } // function getStack

    /**
     * Get item of stack by number
     * @param integer $nNumber
     * @return \fan\core\service\matcher\item
     * @throws \fan\project\exception\service\fatal
     */
    public function getItem($nNumber)
    {
        if (!isset($this->oStack[$nNumber])) {
            throw new \fan\project\exception\service\fatal($this, 'Requested item number "' . $nNumber . '" isn\'t set');;
        }
        return $this->oStack[$nNumber];
    } // function getItem

    /**
     * Get Last item of stack
     * @return \fan\core\service\matcher\item
     */
    public function getLastItem()
    {
        return $this->getItem($this->getLastIndex());
    } // function getLastItem

    /**
     * Get Current item of stack
     * @return \fan\core\service\matcher\item
     */
    public function getCurrentItem()
    {
        return $this->getItem($this->getCurrentIndex());
    } // function getCurrentItem

    // ============= Get URI ============= \\
    /**
     * Get URI by number
     * @param integer $nNumber
     * @return \fan\core\service\matcher\item\uri
     */

    public function getUri($nNumber)
    {
        $oItem = $this->getItem($nNumber);
        return $oItem['uri'];
    } // function getUri

    /**
     * Get last URI
     * @return \fan\core\service\matcher\item\uri
     */
    public function getLastUri()
    {
        return $this->getUri($this->getLastIndex());
    } // function getLastUri

    /**
     * Get Current URI
     * @return \fan\core\service\matcher\item\uri
     */
    public function getCurrentUri()
    {
        return $this->getUri($this->getCurrentIndex());
    } // function getCurrentUri

    // ============= Get Handler ============= \\
    /**
     * Get Handler by number
     * @param integer $nNumber
     * @return \fan\core\service\matcher\item\handler
     */
    public function getHandler($nNumber)
    {
        $oItem = $this->getItem($nNumber);
        return $oItem['handler'];
    } // function getHandler

    /**
     * Get Current Handler
     * @return \fan\core\service\matcher\item\handler
     */
    public function getCurrentHandler()
    {
        return $this->getHandler($this->getCurrentIndex());
    } // function getCurrentHandler

    // ============= Get Parsed data ============= \\
    /**
     * Get Parsed Data by number
     * @param integer $nNumber
     * @return \fan\core\service\matcher\item\parsed
     */
    public function getParsedData($nNumber)
    {
        $oItem = $this->getItem($nNumber);
        return $oItem['parsed'];
    } // function getParsedData

    /**
     * Get Last URI
     * @return \fan\core\service\matcher\item\parsed
     */
    public function getLastParsedData()
    {
        return $this->getParsedData($this->getLastIndex());
    } // function getLastParsedData

    /**
     * Get Current URI
     * @return \fan\core\service\matcher\item\parsed
     */
    public function getCurrentParsedData()
    {
        return $this->getParsedData($this->getCurrentIndex());
    } // function getCurrentParsedData

} // class \fan\core\service\matcher
?>