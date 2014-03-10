<?php namespace fan\core\service\template\parser;
/**
 * Template parser engine form
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class image extends base
{
    /**
     * @var array Defined tpl-tag list
     */
    protected $aTagList = array('some_special');

    /**
     * Parse some special
     * @return string
     */
    public function parse_some_special()
    {
        return '$sReturnHtmlVal.=$this->getSpecial();' . "\n";
    } // function parse_some_special

} // class \fan\core\service\template\parser\image
?>