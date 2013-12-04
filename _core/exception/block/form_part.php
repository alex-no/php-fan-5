<?php namespace core\exception\block;
/**
 * Exception a block fatal error
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
class form_part extends fatal
{
    /**
     * @var block_html_form_base Parsed form block
     */
    protected $oFormPart = null;

    /**
     * @var string Public error message
     */
    protected $aErrorMsg = null;

    /**
     * Exception's constructor
     * @param block_html_form_base $oBlock Object - instance of form block
     * @param array $aErrorMsg Error message
     * @param integer $nCcode Error Code
     */
    public function __construct($oBlock, $aErrorMsg, $nCode = E_USER_WARNING)
    {
        $this->oFormPart = $oBlock;
        $this->aErrorMsg = $aErrorMsg;
        parent::__construct(implode('', $aErrorMsg), $nCode);
    } // function __construct

    /**
     * Get Block Name
     * @return string
     */
    public function getBlockName()
    {
        return $this->oFormPart->getBlockName();
    } // function getBlockName

    /**
     * Get array of Error Messages
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->aErrorMsg;
    } // function getErrorMessages

} // class \core\exception\block\form_part
?>