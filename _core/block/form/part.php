<?php namespace fan\core\block\form;
/**
 * Part of form block abstract
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class part extends usual
{
    // ------------ Functions for other parts ------------ \\
    /**
     * Init Current Form Part - usually set data and default value there
     * Method for redefine
     * @param \fan\core\block\form\parser $oMainFormBlock Main form part block
     */
    protected function partInit($oMainFormBlock = NULL)
    {
    } // function partInit

    /**
     * Parse Current Form Part
     * @param boolean $bParceEmpty allow parse if form is empty
     * @param boolean $bParsingCondition (null - parse by Meta-condition, true - always parse, false - don't parse )
     * @param boolean $bAllowTransfer allow Transfer after submit
     * @param boolean $bShowWarning allow warning about parse part of form
     * @return array
     */
    protected function _parseForm($bParceEmpty = true, $bParsingCondition = false, $bAllowTransfer = false, $bShowWarning = true)
    {
        if ($bShowWarning) {
            trigger_error('Do not run method "_parseForm" in part of form. It was runned in block "' . $this->blockName . '".', E_USER_WARNING);
        } else {
            parent::_parseForm($bParceEmpty, $bParsingCondition, $bAllowTransfer);
        }
        return $this->getForm()->isError();
    } // function _parseForm
} // class \fan\core\block\form\part
?>