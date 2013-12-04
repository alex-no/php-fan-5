<?php
/**
 * Main entry point for application requests
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
if (substr($_SERVER['HTTP_HOST'], 0, 4) != 'www.') {
    $sProtocol = @$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    header('Location: ' . $sProtocol . 'www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
} else {
    //$nStart = microtime(true); $aPoints = array();
    if (!defined('BASE_DIR')) {
        define('BASE_DIR', __DIR__);
    }

    // Change the path below to match the way to "bootstrap.php" from the current directory.
    require_once __DIR__ . '/../_core/bootstrap.php';

    // Change the path below to match the way to "bootstrap.ini" from the current directory.
    \bootstrap::run(__DIR__ . '/../_project/conf/bootstrap.ini');

    //echo microtime(true) - $nStart; echo '<pre>'; print_r($aPoints); echo '</pre>';
}
?>