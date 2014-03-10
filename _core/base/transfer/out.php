<?php namespace fan\core\base\transfer;
/**
 * Outer transfer
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
class out extends \fan\core\base\transfer
{
    /**
     * Transfer's constructor
     * @param string $sNewUrn New Transfer's URL
     * @param string $sNewQueryString New Query String
     * @param string $sDbOper Database Operation (commit, rollback)
     */
    public function __construct($sNewUrn, $sNewQueryString = null, $sDbOper = null)
    {
        $this->sTransferType = 'out';
        if ($sDbOper != 'rollback') {
            $sDbOper = 'commit';
        }
        parent::__construct($sNewUrn, $sNewQueryString, $sDbOper);
    }
} // class \fan\core\base\transfer\out
?>