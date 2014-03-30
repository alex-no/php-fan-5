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
 * @version of file: 05.02.002 (31.03.2014)
 */

if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
}
// Change the path below to match the way to "bootstrap.php" from the current directory.
require_once __DIR__ . '/../_core/bootstrap.php';
// Change the path below to match the way to "bootstrap.ini" from the current directory.
\bootstrap::run(__DIR__ . '/../_project/conf/bootstrap.ini');
?>