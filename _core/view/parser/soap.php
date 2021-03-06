<?php namespace fan\core\view\parser;
/**
 * View parser SOAP-type
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class soap extends \fan\core\view\parser
{
    // ======== Static methods ======== \\
    /**
     * Get View-Format
     * @return string
     */
    final static public function getFormat() {
        return 'soap';
    } // function getFormat

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    // ======== Protected methods ======== \\
} // class \fan\core\view\parser\soap
?>