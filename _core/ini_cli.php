<?php
/**
 * Init CLI operation
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
 * @version of file: 05.01.001 (29.09.2011)
 */
if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname($_SERVER['SCRIPT_FILENAME']));
}

require_once __DIR__ . '/bootstrap.php';

if (file_exists(__DIR__ . '/../_project/ini_cli.php')) {
    include __DIR__ . '/../_project/ini_cli.php';
} else {
    \bootstrap::init(__DIR__ . '/../_project/conf/bootstrap.ini');
}
?>