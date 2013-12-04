<?php namespace core\base\transfer;
/**
 * Internal transfer
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
class int extends \core\base\transfer
{
    /**
     * Transfer's constructor
     * @param string $sNewUrn New Transfer's URL
     * @param string $sNewQueryString New Query String
     * @param string $sDbOperation Database Operation (commit, rollback)
     */
    public function __construct($sNewUrn, $sNewQueryString = null, $sDbOperation = null)
    {
        $this->sTransferType = 'int';
        parent::__construct($sNewUrn, $sNewQueryString, $sDbOperation);
    }
} // class \core\base\transfer\int
?>