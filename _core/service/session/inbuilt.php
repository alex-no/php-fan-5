<?php namespace core\service\session;
/**
 * PHP native session engine
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
 * @version of file: 02.002
 */
class inbuilt
{
    /**
     * Facade of service
     * @var core\service\session
     */
    protected $oFacade = null;

    /**
     * Constructor
     * @param array $aConfig Configuration data
     */
    public function __construct()
    {
        session_start();
    } // function __construct

    /**
     * Set Facade
     * @param \core\service\session $oFacade
     * @return \core\service\session\inbuilt
     */
    public function setFacade(\core\service\session $oFacade)
    {
        if (empty($this->oFacade)) {
            $this->oFacade = $oFacade;
        }
        return $this;
    } // function setFacade

    /**
     * Get Session ID
     * @return string Session ID
     */
    public function getSessionId()
    {
        return session_id();
    } // getSessionId

    /**
     * Set Session ID
     * @param string $sSid
     * @return \core\service\session\inbuilt
     */
    public function setSessionId($sSid)
    {
        session_id($sSid);
        return $this;
    } // setSessionId

    /**
     * Get Session Name
     * @return string Session Name
     */
    public function getSessionName()
    {
        return session_name();
    } // getSessionName

    /**
     * Get Session data
     * @param string $sGroup The default value
     * @param string $sSesName The Session key
     * @return mixed Session parameter
     */
    public function &getData($sGroup, $sSesName)
    {
        if (!isset($_SESSION[$sGroup])) {
            $_SESSION[$sGroup] = array($sSesName => null);
        } elseif (!is_array($_SESSION[$sGroup]) || !array_key_exists($sSesName, $_SESSION[$sGroup])) {
            $_SESSION[$sGroup][$sSesName] = null;
        }
        return $_SESSION[$sGroup][$sSesName];
    } // function getData

    /**
     * Get Session data
     * @return mixed Session parameter
     */
    public function &getRoot()
    {
        return $_SESSION;
    } // function getRoot

    /**
     * Destroy the session
     * @return \core\service\session\inbuilt
     */
    public function destroy()
    {
        @session_destroy();
        return $this;
    } // function destroy
} // class core\service\session\inbuilt
?>