<?php namespace fan\core\service;
/**
 * Cron-timer manager service
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
 */
class timer extends \fan\core\base\service\single
{
    /**
     * Limit jointly runned program (default)
     */
    const JOINTLY_LIMIT_DEFAULT = 5;

    /**
     * Max count jointly runned program
     */
    const JOINTLY_LIMIT_MAX = 32;

    /**
     * @var sting EntityName
     */
    protected $sEttName;
    /**
     * @var sting Base Path to timer classes
     */
    protected $sBasePath;
    /**
     * @var sting Base NameSpace to timer classes
     */
    protected $sBaseNS;

    /**
     * Service's constructor
     */
    protected function __construct()
    {
        parent::__construct();
        $this->sEttName  = $this->getConfig('ENTITY', 'timer_program');
        $this->sBasePath = \bootstrap::parsePath($this->getConfig('TIMER_DIR', '{PROJECT}/cli/timer/'));
        $this->sBaseNS   = $this->getConfig('BASE_NS', '\fan\project\cli\timer');
    } // function __construct

    /**
     * Charge new Program for run
     * @param mixed $mStartTime - date (string) or time shift from current (integer in sec)
     * @param string $sClassName
     * @param string $sMethodName
     * @param array $aParam
     * @param number $nPeriod - period of run this program
     * @param number $nOvercall - overcall limit ("-1" - no limits, "0" - disable any overcall, ">0" - exactly limit)
     * @param boolean $bIsShell - use shell fo run program
     * @return string - Id of Process
     */
    public function chargeProgram($mStartTime, $sClassName, $sMethodName, $aParam, $nPeriod = 0, $nOvercall = null, $bIsShell = true)
    {
        $sStartTime = is_numeric($mStartTime) ? service('date', array(date('Y-m-d H:i:s'), 'mysql'))->shiftDate($mStartTime) : $mStartTime;
        if (!$sStartTime) {
            return null;
        }
        $oRow = gr($this->sEttName);
        $oRow->setFields(array(
            'start_time'     => $sStartTime,
            'class_name'     => $sClassName,
            'method_name'    => $sMethodName,
            'parameters'     => $aParam,
            'period'         => $nPeriod,
            'overcall_limit' => is_null($nOvercall) ? ($nPeriod > 0 ? 0 : -1) : $nOvercall,
            'overcall_qtt'   => 0,
            'is_active'      => 0,
            'last_start'     => null,
        ), true);

        if ($bIsShell && $this->getConfig('ENABLE_EXEC') && $this->getConfig('IS_AT_COMMAND')) {
            // Emulate work of Crontab-line by "at"-command in Windows
            $sCommand = $this->_getCommandLine('CRON_FILE') . $oRow->getId();

            $aDate = service('date', array($oRow->get_start_time(), 'mysql'))->getDateAsArray();
            $aDate[4] += ($aDate[5] > 55 ? 2 : 1);
            if ($aDate[4] > 59) {
                $aDate[4] -= 60;
                $aDate[3]++;
                if ($aDate[3] > 23) {
                    $aDate[3] -= 24;
                    $aDate[2]++;
                }
            }

            $sCommand = 'at ' . $aDate[3] . ':' . $aDate[4] . ($aDate[2] != date('d') ? ' /next:' . $aDate[2] : '') . ' ' . $sCommand;
            $this->execBackBin($sCommand);
        }

        return $oRow->getId();
    } // function chargeProgram

    /**
     * Modify existing Program
     * @param string $sClassName
     * @param string $sMethodName
     * @param array  $aParam
     * @param number $nPeriod - period of run this program
     * @param number $nOvercall - allowed overcall quantity
     * @return string - Id of Process
     */
    public function modifyChargedProgram($sClassName, $sMethodName, $aParam = null, $nPeriod = null, $nOvercall = null)
    {
        $oRow = ge($this->sEttName)->getRowByParam(array(
            'class_name'  => $sClassName,
            'method_name' => $sMethodName,
        ));

        if ($oRow->checkIsLoad()) {
            $this->_modifyProgram($oRow, $aParam, $nPeriod, $nOvercall);
            return $oRow->getId();
        }
        return $this->chargeProgram(0, $sClassName, $sMethodName, $aParam, is_null($nPeriod) ? 0 : $nPeriod, $nOvercall);
    } // function modifyChargedProgram

    /**
     * Modify existing Program by process ID
     * @param string $sPID
     * @param array  $aParam
     * @param number $nPeriod - period of run this program
     * @param number $nOvercall - allowed overcall quantity
     * @return string - Id of Process
     */
    public function modifyChargedProgramByPID($sPID, $aParam = null, $nPeriod = null, $nOvercall = null)
    {
        $oRow = gr($this->sEttName, $sPID);
        if ($oRow->checkIsLoad()) {
            $this->_modifyProgram($oRow, $aParam, $nPeriod, $nOvercall);
            return $sPID;
        }
        return null;

    } // function modifyChargedProgramByPID

    /**
     * run Timer Program
     * @param string $sPID
     */
    public function runCronProgram($sPID = null)
    {
        if ($sPID) {
            $oRow = gr($this->sEttName, $sPID);
            if ($oRow->checkIsLoad()) {
                $this->_runProgram($oRow);
                return true;
            }
            return false;
        } else {
            $oEtt = ge($this->sEttName);
            if ($this->getConfig('ENABLE_EXEC')) {
                $oRowset = $oEtt->getRowsetByParam('start_time <= \'' . date('Y-m-d H:i:s') . '\'', -1, -1, 'ORDER BY `last_start`');
                $nJointlyLimit = $this->getConfig('JOINTLY_LIMIT', self::JOINTLY_LIMIT_DEFAULT);
                if ($nJointlyLimit > self::JOINTLY_LIMIT_MAX) {
                    $nJointlyLimit = self::JOINTLY_LIMIT_MAX;
                }
                $nIteration = 0;
                foreach ($oRowset as $e) {
                    $this->_runBackground($e->getId());
                    $nIteration++;
                    if ($nIteration >= $nJointlyLimit) {
                        break;
                    }
                }
            } else {
                $oRowset = $oEtt->getRowsetByParam('start_time <= \'' . date('Y-m-d H:i:s') . '\'', 1, -1, 'ORDER BY IF(`period`=0, IF(`is_active`=0, 0, 3), IF(`is_active`=0, 1, 2)), `last_start`');
                if (count($oRowset) > 0) {
                    $this->_runProgram($oRowset[0]);
                }
            }
        }
        return true;
    } // function runCronProgram
    /**
     * Run php program in background
     * @param string $sClassName
     * @param string $sMethodName
     * @param array  $aParam
     * @param number $nOvercall - allowed overcall quantity
     */
    public function execBackPhp($sClassName, $sMethodName, $aParam, $nOvercall = -1)
    {
        $bIsExec = $this->getConfig('ENABLE_EXEC');
         // If process is started by PID shift time for many hour ahed so disable casual run by CRON
        $sPID = $this->chargeProgram($bIsExec ? 10000 : 0, $sClassName, $sMethodName, $aParam, 0, $nOvercall, false);
        if ($bIsExec) {
            ge($this->sEttName)->getConnection()->commit(); // ToDo: rebuild it
            $this->_runBackground($sPID);
        }
        return $sPID;
    }

    /**
     * Exec binary program in background
     * @param string $sCmd
     */
    public function execBackBin($sCmd)
    {
        if ($this->getConfig('ENABLE_EXEC')) {
            if (substr(php_uname(), 0, 7) == 'Windows'){ // || !function_exists('exec')
                if (function_exists('popen') && function_exists('pclose')) {
                    pclose(popen('start /B ' . $sCmd, 'r'));
                    return true;
                }
            } elseif (function_exists('exec')) {
                exec($sCmd . ' > /dev/null &');
                return true;
            }
        }
        return false;
    }

    // =========================================================== \\

    /**
     * Run One Timer Program
     * @param \fan\model\timer_program\row $oTimerRow
     * @return \fan\core\service\timer
     */
    protected function _runProgram($oTimerRow)
    {
        $oError = service('error');
        /* @var $oError \fan\core\service\error */
        $sClassName = $oTimerRow->get_class_name();

        // Check overcall before run Timer-class
        $nPeriod = $oTimerRow->get_period();
        if ($oTimerRow->get_is_active()) {
            $nQttLimit = $oTimerRow->get_overcall_limit();
            if ($nQttLimit < 0 && !$nPeriod) {
                return $this;
            }
            if ($nQttLimit > -1) {
                $nQtt = $oTimerRow->get_overcall_qtt() + 1;
                service('log')->logMessage('overcall', 'Quantity of owercall is ' . $nQtt . ($nQtt > $nQttLimit ? ".\nIt is critical quantity (limit = " . $nQttLimit . ').' : '.'), 'Timer program overcall', $sClassName);
                if ($nQtt > $nQttLimit) {
                    $oError->makeErrorEmail('overcall', 'Timer program overcall', 'Quantity of owercall (' . $nQtt . ") is more limit.\n\n" . $sClassName);
                    if (!$nPeriod) {
                        return $this; // Return after one-call procedure
                    }
                } else {
                    $oTimerRow->set_overcall_qtt($nQtt);
                    $oTimerRow->save();
                    $oTimerRow->getEntity()->getConnection()->commit();
                    return $this; // Return after allowed overcall
                }
            }
        }

        // Prepare time-parameters before run Timer-class
        $oTimerRow->setFields(array(
            'overcall_qtt' => 0,
            'is_active'    => 1,
            'last_start'   => date('Y-m-d H:i:s'),
        ));
        $oPrevDate = service('date', array($oTimerRow->get_start_time(), 'mysql'));
        if ($nPeriod > 0) {
            $nDifference = $oPrevDate->getDifference(date('Y-m-d H:i:s'));
            $sStartTime  = $oPrevDate->shiftDate($nPeriod * ceil($nDifference / $nPeriod));
            if ($sStartTime) {
                $oTimerRow->set_start_time($sStartTime);
            }
        }
        $oTimerRow->save();
        $oTimerRow->getEntity()->getConnection()->commit();

        // Check Timer-class
        $sPath = $this->sBasePath . $sClassName . '.php';
        if (!file_exists($sPath)) {
            $oError->logErrorMessage('File "'. $sPath . '" for timer doesn\'t exists.', 'Error run timer proggamm');
            return $this;
        }
        require_once($sPath);
        $sClassName = '\\' . trim($this->sBaseNS, '\\') . '\\' . $sClassName;
        if (!class_exists($sClassName, false)) {
            $oError->logErrorMessage('Class "'. $sClassName . ' in the file "' . $sPath . '" for timer doesn\'t exists.', 'Error run timer proggamm');
            return $this;
        }
        $oObj = new $sClassName();
        if (!$oObj instanceof \fan\core\base\timer_program) {
            $oError->logErrorMessage('Class "'. $sClassName . ' isn\'t instance of \fan\core\base\timer_program.', 'Error run timer proggamm');
            return $this;
        }

        // Run Timer-class
        $oObj->setTimerRow($oTimerRow);
        call_user_func_array(array($oObj, $oTimerRow->get_method_name()), $oTimerRow->get_parameters());

        // Fix result of Timer-class
        $nPeriod2 = $oObj->getPeriod();
        if ($nPeriod2 > 0) {
            $nDifference = $oPrevDate->getDifference(date('Y-m-d H:i:s'));
            $sStartTime  = $oPrevDate->shiftDate($nPeriod2 * ceil($nDifference / $nPeriod2));
            if ($sStartTime) {
                $oTimerRow->set_start_time($sStartTime);
            }
            $oTimerRow->set_is_active(0);
            $oTimerRow->save();
        } elseif ($oTimerRow->checkIsLoad()) {
            $oTimerRow->delete();
        }
        $oTimerRow->getEntity()->getConnection()->commit();

        return $this;
    } // function _runProgram

    /**
     * Modify existing Program
     * @param timer_program $oRow
     * @param array  $aParam
     * @param number $nPeriod - period of run this program
     * @param number $nOvercall - allowed overcall quantity
     */
    protected function _modifyProgram($oRow, $aParam, $nPeriod, $nOvercall)
    {
        if (!is_null($aParam)) {
            $oRow->set_parameters($aParam);
        }
        if (!is_null($nPeriod)) {
            $oRow->set_period($nPeriod);
        }
        if (!is_null($nOvercall)) {
            $oRow->set_overcall_limit($nOvercall);
        }
        $oRow->save();
    } // function _modifyProgram

    /**
     * Run php program in background
     * @param string $sPID
     * @return boolean
     */
    protected function _runBackground($sPID)
    {
        return $this->execBackBin($this->_getCommandLine('BGR_FILE') . $sPID);
    } // function _runBackground

    /**
     * Get string of command line
     * @param string $sKey
     * @return string
     */
    protected function _getCommandLine($sKey)
    {
        $sSeparator = defined('DIR_SEPARATOR') ? DIR_SEPARATOR : '/';
        $sCommand   = str_replace($sSeparator, DIRECTORY_SEPARATOR, \bootstrap::parsePath($this->getConfig($sKey)));
        return $this->getConfig('PHP_INTERPRETER') . ' ' . $sCommand . ' ';
    } // function _getCommandLine

} // class \fan\core\service\timer
?>