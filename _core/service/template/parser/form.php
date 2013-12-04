<?php namespace core\service\template\parser;
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
 * @version of file: 05.001 (29.09.2011)
 */
class form extends base
{
    /**
     * @var array Defined tpl-tag list
     */
    protected $aTagList = array('form_key_field', 'form_sid');

    /**
     * Parse form key field
     * @return string
     */
    public function parse_form_key_field()
    {
        return '$sReturnHtmlVal.=$this->getKeyField();' . "\n";
    } // function parse_form_key_field

    /**
     * Parse form SID
     * @return string
     */
    public function parse_form_sid()
    {
        return '$sReturnHtmlVal.=$this->getSidField();' . "\n";
    } // function parse_form_sid

} // class \core\service\template\parser\form
?>