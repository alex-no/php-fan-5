<?php namespace fan\core\service\matcher;
/**
 * Description of stack
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
class stack extends \ArrayIterator
{
    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected $oFacade = null;
    /**
     * @var integer Index of current URI
     */
    protected $iCurrent = 0;

    /**
     * Set Facade
     * @param \fan\core\base\service $oFacade
     * @return stack
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        $this->oFacade = $oFacade;

        return $this;
    } // function setFacade

    /**
     * Set New Item of Stack
     * @param string $sRequest
     * @param string $sPosition
     * @return stack
     */
    public function setNewItem($sRequest, $sPosition = null, $bShiftCurrent = true)
    {
        $iIndex = count($this);
        if ($bShiftCurrent) {
            $this->iCurrent = $iIndex;
        }

        $oItem = new \fan\project\service\matcher\item($iIndex);
        $this[$iIndex] = $oItem;
        $oItem->setFacade($this->oFacade);

        if (\bootstrap::isCli()) {
            $oItem->initCli($sRequest, $sPosition);
            // Pre-Parse Request
            //$oItem->preParseRequest($bShiftCurrent);
        } else {
            $oItem->initOut($sRequest, $sPosition);
            // Pre-Parse Request
            $oItem->preParseRequest($bShiftCurrent);
        }

        return $this;
    } // function setNewItem

    /**
     * Get Current Index of Uri
     * @return integer
     */
    public function getLastIndex()
    {
        return count($this) - 1;
    } // function getLastIndex

    /**
     * Get Current Index of Uri
     * @return integer
     */
    public function getCurrentIndex()
    {
        return $this->iCurrent;
    } // function getCurrentIndex

} // class \fan\core\service\matcher\stack
?>