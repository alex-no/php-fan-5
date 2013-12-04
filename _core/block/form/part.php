<?php namespace core\block\form;
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
 * @version of file: 05.001 (29.09.2011)
 * @abstract
 */
abstract class part extends usual
{
    // ------------ Functions for other parts ------------ \\
    /**
     * Init Current Form Part
     * Method for redefine
     * @param mixed $mData data from main part
     */
    protected function partInit($mData = NULL)
    {
    } // function partInit

    /**
     * Parse Current Form Part
     * @param boolean $bParceEmpty allow parse if form is empty
     * @param boolean $bParsingCondition (null - parse by Meta-condition, true - always parse, false - don't parse )
     * @param boolean $bAllowTransfer allow Transfer after submit
     * @return array
     */
    protected function _parseForm($bParceEmpty = true, $bParsingCondition = true, $bAllowTransfer = false)
    {
        if (parent::_parseForm($bParceEmpty, $bParsingCondition, $bAllowTransfer)) {
            return $this->aFieldValue;
        }
        if ($this->bIsError) {
            throw new \project\exception\block\form_part($this, $this->aErrorMsg);
        }
        return array();
    } // function _parseForm
} // class \core\block\form\part
?>