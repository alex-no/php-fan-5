<?php namespace fan\core\view\router;
/**
 * View router of JSON-block
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
class json extends \fan\core\view\router\simple
{
    /**
     * Use Base64
     * @var boolean
     */
    protected $bUseBase64 = false;
    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    /**
     * Set flag of Use Base-64 for JSON-data
     * @param boolean $bUseBase64
     * @return \fan\core\view\router\json
     */
    public function useBase64($bUseBase64 = true)
    {
        $this->bUseBase64 = $bUseBase64;
        return $this;
    } // function useBase64
    /**
     * Get flag of Use Base-64
     * @return boolean
     */
    public function isUseBase64()
    {
        return $this->bUseBase64;
    } // function isUseBase64

 } // class \fan\core\view\router\json
?>