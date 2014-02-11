<?php namespace core\base\meta;
/**
 * Class for get delayed meta-data, after make block
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
 * @version of file: 04.001 (11.07.2011)
 */
class delayed
{
    /**
     * @var mixed Called object or Class name
     */
    protected $mObj;
    /**
     * @var string Metod name
     */
    protected $sMethod;
    /**
     * @var array Arguments of called method
     */
    protected $aArguments;

    /**
     * Delayed meta constructor
     * @param mixed  $mObj
     * @param string $sMethod
     * @param mixed  $mArguments
     */
    public function __construct($mObj, $sMethod, $mArguments)
    {
        $this->mObj       = $mObj;
        $this->sMethod    = $sMethod;
        $this->aArguments = is_null($mArguments) ? array() : (is_array($mArguments) ? $mArguments : array($mArguments));
    } // function __construct

    /**
     * Get Dynamic Meta Value
     * @return mixed
     */
    public function getValue()
    {
        return call_user_func_array(array($this->mObj, $this->sMethod), $this->aArguments);
    } // function getValue

} // class \core\base\meta\delayed
?>