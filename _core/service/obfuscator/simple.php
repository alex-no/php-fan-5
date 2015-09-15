<?php namespace fan\core\service\obfuscator;
use fan\project\exception\service\fatal as fatalException;
/**
 * Simple obfuscator by regexp
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
 * @version of file: 05.02.008 (15.09.2015)
 */
class simple extends base
{

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    /**
     * Obfuscate string of Content
     * @param string $sText
     * @return string
     */
    public function obfuscate($sText)
    {
        if ($this->bDropComments) {
            $sText = preg_replace('/\/\*.+?\*\//s', ' ', $sText);
        }
        if ($this->bDropComments || $this->bDropEndRow) {
            $sText = preg_replace('/^\s*\/\/.*$/m', ' ', $sText);
        }
        if ($this->bDropEndRow) {
            $sText = preg_replace('/[\t\n\r]+/', ' ', $sText);
        }
        if ($this->bSpacesToOne) {
            $sText = preg_replace('/\s{2,}/', ' ', $sText);
        }
        return $sText;
    } // function obfuscate

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\


} // class \fan\core\service\obfuscator\simple
?>