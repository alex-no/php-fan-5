<?php namespace fan\core\service\session;
/**
 * Session engine adodb
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
 * @version of file: 05.02.001 (10.03.2014)
 */
include_once __DIR__ . '/session_engine_php.php';

/**
 * ADOdb session engine
 * @version 1.0
 */
class adodb extends inbuilt
{
    /**
     * Constructor
     * @param array $aConfig Configuration data
     */
    public function __construct($aConfig)
    {
        global $ADODB_SESSION_DRIVER, $ADODB_SESSION_CONNECT, $ADODB_SESSION_USER, $ADODB_SESSION_PWD, $ADODB_SESSION_DB, $ADODB_SESSION_TBL;
        require_once \bootstrap::get_dir('LIBS') . '/ADOdb/adodb.inc.php';

        if (@$aConfig['IS_DATABASE']) {
            $aDbConfig = \fan\project\service\config::instance()->get('database');
            $aDb = $aDbConfig['DATABASES'][$aConfig['CONNECTION']];

            $ADODB_SESSION_DRIVER  = $aDb['DRIVER'];
            $ADODB_SESSION_CONNECT = $aDb['HOST'];
            $ADODB_SESSION_USER    = $aDb['USER'];
            $ADODB_SESSION_PWD     = $aDb['PASSWORD'];
            $ADODB_SESSION_DB      = $aDb['DATABASE'];
            $ADODB_SESSION_TBL     = $aConfig['TABLE'];

            require_once \bootstrap::get_dir('LIBS') . '/ADOdb/session/adodb-session.php';
        } // check database

        parent::__construct($aConfig);
    } // function __construct
} // class \fan\core\service\session\adodb
?>