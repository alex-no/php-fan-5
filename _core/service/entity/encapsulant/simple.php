<?php namespace core\service\entity\encapsulant;
/**
 * Description of encapsulant
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
 * @version of file: 05.001
 */
class simple
{
    /**
     * Class of DB-table
     * @var core\entity\table
     */
    protected $oService = null;

    /**
     * Encapsulant-assistant constructor
     * @param \core\service\entity $oService
     */
    public function __construct(\core\service\entity $oService)
    {
        $this->oService = $oService;
    } // function __construct

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * Encrypt id
     * @param number $nId - id
     * @return string encrypted id
     */
    public function encryptId($nId)
    {
        $sCode1 = '';
        if ($nId) {
            $nShift = rand(0, 30);
            $nShift = $nShift % 31;
            $sCriptKey = $this->_getCriptKey($this->_code2Symbol($nShift));

            $sCode0 = str_pad(($nId < 1000000000 ? $nId * ($nShift + 1) : ($nId < 100000000000 ? $nId + $nShift : $nId)), 20, '0', STR_PAD_LEFT);
            $sCode0 = $this->_getCheckSumId($sCode0, 11) . $sCode0;
            if($nShift) {
                $sCode0 = substr($sCode0, $nShift) . substr($sCode0, 0, $nShift);
            }
            for ($i = 0; $i < 31; $i++) {
                $n = $this->_symbol2Code($sCriptKey{$i});
                $sCode1 .= $this->_code2Symbol(($n + (int)$sCode0{$i}) % 36);
            }
            $nShiftPos = $this->_getShiftPos();
            $sCode1 = $nShiftPos ? substr($sCode1, 0, $nShiftPos) . $this->_code2Symbol($nShift) . substr($sCode1, $nShiftPos) : $this->_code2Symbol($nShift) . $sCode1;
        }
        return $sCode1 ? $sCode1 : '0';
    }// function encryptId

    /**
     * decrypt id
     * @param string $sCode - encrypted id
     * @return number decrypted id
     */
    public function decryptId($sCode)
    {
        if(strlen($sCode) != 32) {
            return 0;
        }
        $nShiftPos = $this->_getShiftPos();
        $nShift = $this->_symbol2Code($sCode{$nShiftPos}) % 31;
        $sCode1 = substr($sCode, 0, $nShiftPos) . substr($sCode, $nShiftPos + 1);
        $sCriptKey = $this->_getCriptKey($sCode{$nShiftPos});
        $sCode0 = '';
        for ($i = 0; $i < 31; $i++) {
            $n1 = $this->_symbol2Code($sCode1{$i});
            $n2 = $this->_symbol2Code($sCriptKey{$i});
            $sCode0 .= ($n1 >= $n2 ? $n1 - $n2 : $n1 + 36 - $n2);
        }
        if($nShift) {
            $sCode0 = substr($sCode0, -$nShift) . substr($sCode0, 0, -$nShift);
        }

        $sCode2 = substr($sCode0, 11);
        if($this->_getCheckSumId($sCode2, 11) == substr($sCode0, 0, 11)) {
            while (strlen($sCode2) > 0 && $sCode2{0} == '0') {
                $sCode2 = substr($sCode2, 1);
            }
            return strlen($sCode2) <= 9 ? $sCode2 / ($nShift + 1) : (strlen($sCode2) < 12 ? $sCode2 - $nShift : $sCode2);
        } else {
            return 0;
        }
    }// function decryptId

    // ======== Private/Protected methods ======== \\
    /**
     * Convert code 0-35 into sybols a-z0-9
     * @param integer $nCode
     * @return string symbol
     */
    protected function _code2Symbol($nCode)
    {
        return $nCode > 25 ? chr($nCode + 22) : chr($nCode + 97);
    }// function _code2Symbol
    /**
     * Convert sybols a-z0-9 into code 0-35
     *
     * @param string $sSym
     * @return integer code
     */
    protected function _symbol2Code($sSym)
    {
        $nCode = ord($sSym);
        return $nCode > 96 ? $nCode - 97 : $nCode - 22;
    }// function _symbol2Code

    /**
     * Get Cript Key
     *
     * @param string $sSrt
     * @param integer $nLen
     * @return string
     */
    protected function _getCriptKey($sSrt, $nLen = 31)
    {
        return substr(md5($this->_getEncryptKey() . $sSrt), -$nLen);
    }// function _getCriptKey
    /**
     * Get Check Sum
     *
     * @param integer $nId
     * @param integer $nLen
     * @return string
     */
    protected function _getCheckSumId($nId, $nLen = 11)
    {
        $sCs = substr(sprintf('%u', crc32($nId . $this->_getEncryptKey())), -$nLen);
        return str_pad($sCs, $nLen, '0', STR_PAD_LEFT);
    }// function _getCheckSumId
    /**
     * Get Shift Position
     *
     * @param integer $nLen - length of cripted sthing
     * @return integer
     */
    protected function _getShiftPos($nLen = 31)
    {
        return sprintf('%u', crc32($this->_getEncryptKey())) % $nLen;
    }// function _getShiftPos

    protected function _getEncryptKey()
    {
        return $this->oService->getConfig()->get('encrypt_id_key', '');
    }// function _getEncryptKey

} // class \core\service\entity\encapsulant\simple
?>