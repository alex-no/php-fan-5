<?php namespace fan\core\service\session;
/**
 * PEAR session engine
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class pear
{
    /**
     * Constructor
     * @param \fan\core\service\config\row $oConfig Configuration data
     */
    public function __construct($oConfig)
    {
        app_set_include_path(\bootstrap::get_dir('LIBS') . '/PEAR');
        require_once @'HTTP/Session.php';

        if ($oConfig['IS_DATABASE']) {
            $aDbConfig = \fan\project\service\config::instance()->get('database');
            $aDb = $aDbConfig['DATABASES'][$oConfig['CONNECTION']];
            HTTP_Session::setContainer('DB', array(
                'dsn'   => $aDb['DRIVER'] . '://' . $aDb['USER'] . ':' . $aDb['PASSWORD'] . '@' . $aDb['HOST'] . '/' . $aDb['DATABASE'],
                'table' => $oConfig['TABLE']));
        } // check database

        HTTP_Session::useCookies(true);
        HTTP_Session::start($oConfig['SESSION_NAME'], \fan\project\service\request::instance()->get($oConfig['SESSION_NAME']));
    } // function __construct

    /**
     * Get Session ID
     * @return string Session ID
     */
    public function getSessionId()
    {
        return HTTP_Session::id();
    } // function getSessionId

    /**
     * Get Session parameter
     * @param string $sKey The Session key
     * @param string $mDefaultValue The default value
     * @return mixed Session parameter
     */
    public function get($sKey, $mDefaultValue = NULL)
    {
        return HTTP_Session::get($sKey, $mDefaultValue);
    } // function get

    /**
     * Set Session parameter
     * @access public
     * @param string $sKey The Session key
     * @param string $mValue The Session value
     */
    public function set($sKey, $mValue)
    {
        HTTP_Session::set($sKey, $mValue);
    } // function set

    /**
     * UnSet Session parameter
     * @param string $sKey The Session key
     */
    public function remove($sKey)
    {
        HTTP_Session::set($sKey, NULL);
    } // function remove

    /**
     * UnSet all Session parameters
     */
    public function remove_all()
    {
        HTTP_Session::clear();
    } // function remove_all

    /**
     * Destroy the session
     */
    public function destroy()
    {
        HTTP_Session::destroy();
    } // function destroy
} // class \fan\core\service\session\pear
?>