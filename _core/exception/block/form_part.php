<?php namespace fan\core\exception\block;
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class form_part extends local
{
    /**
     * Parsed form block
     * @var \fan\core\block\base
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
     * @param integer $nCode Error Code
     * @param \Exception $oPrevious Previous Exception
     */
    public function __construct(\fan\core\block\base $oBlock, $aErrorMsg, $nCode = E_USER_WARNING, $oPrevious = null)
    {
        $this->oFormPart = $oBlock;
        $this->aErrorMsg = $aErrorMsg;
        parent::__construct($oBlock, 'Form part error', $nCode, $oPrevious);
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

    /**
     * Remove property "oBlock" before "var_export" this object
     */
    public function clearProperty()
    {
        $this->_removeEmbededObject('oFormPart')->_removeEmbededObject('oBlock');
    } // function clearProperty

    /**
     * Get operation for Db (nothing) when exception occured
     * @param string $sDbOper
     * @return null|string
     */
    protected function _defineDbOper($sDbOper = 'nothing')
    {
        return parent::_defineDbOper($sDbOper);
    } // function _defineDbOper

} // class \fan\core\exception\block\form_part
?>