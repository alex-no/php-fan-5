<?php namespace fan\core\plain;
/**
 * Respond for request of obfuscate CSS or JS-file
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.008 (15.09.2015)
 */

class obfuscator
{
    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected $oHandler;
    /**
     * Plain config object
     * @var \fan\core\service\obfuscator
     */
    protected $oObfuscator;

    /**
     * Constructor of Plain controller obfuscator
     * @param boolean $bAllowIni
     */
    public function __construct(\fan\core\service\plain $oHandler, $sKey)
    {
        $this->oHandler = $oHandler;

        $aHandle = $oHandler->getHandleData();
        $this->oObfuscator = service('obfuscator', $aHandle['mReqKey']);
    } // function __construct

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Get CSS-content
     * @return array|string
     */
    public function getCss()
    {
        return $this->_getContent();
    } // getCss
    /**
     * Get JS-content
     * @return array|string
     */
    public function getJs()
    {
        return $this->_getContent();
    } // getJs

    // ======== Private/Protected methods ======== \\

    /**
     * Get content OR content outputer
     * @return array|string
     */
    protected function _getContent()
    {
        $sName = service('request')->get(1, 'A');
        $sContent = $this->oObfuscator->getFileData($sName);
        $aHeaders = $this->oObfuscator->getHeaders($sName, strlen($sContent));
        $this->oHandler->setHeaders($aHeaders);
        return $sContent;
    } // function _getContent

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

} // class \fan\core\plain\obfuscator
?>