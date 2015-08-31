<?php
/**
 * Run install-process
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
 * @version of file: 05.02.007 (31.08.2015)
 */
header('Content-Type: text/html; charset=utf-8');
require_once 'incl/header.php';
require_once 'incl/base.php';

$aClasses = array(
    'check_configuration',
    'check_directories',
    'fan_version',
);
foreach ($aClasses as $v) {
    require_once 'incl/' . $v . '.php';
    if (!call_user_func(array($v, 'run'))) {
        break;
    }
}

require_once 'incl/footer.php';
?>