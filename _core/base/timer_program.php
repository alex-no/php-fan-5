<?php namespace fan\core\base;
/**
 * Timer program base
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
 * @version of file: 05.02.007 (31.08.2015)
 * @abstract
 */
abstract class timer_program
{
    /**
     * @var entity_timer_program Entity of timer
     */
    private $oTimerRow = null;

    /**
     * @var number Period of callings
     */
    private $nPeriod = null;


    /**
     * Set current entyty
     * @param entity_timer_program $oTimerRow
     */
    public function setTimerRow($oTimerRow)
    {
        $this->oTimerRow = $oTimerRow;
    } // function setTimerEntity

    /**
     * Get current entyty
     * @return entity_timer_program
     */
    public function getTimerRow()
    {
        return $this->oTimerRow;
    } // function getTimerEntity

    /**
     * Set time for next run this program
     * @param number $nPeriod
     */
    public function setPeriod($nPeriod)
    {
        if($nPeriod >= 0) {
            $this->nPeriod = $nPeriod;
        }
    } // function setPeriod

    /**
     * Get time for next run this program
     * @return number
     */
    public function getPeriod()
    {
        return is_null($this->nPeriod) ? $this->getTimerRow()->get_period(0, true) : $this->nPeriod;
    } // function getPeriod

} // class timer_program_base
?>