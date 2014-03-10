<?php namespace fan\core\service\log;
/**
 * Base parser of log file
 * array (
 *     0 => offset,
 *     1 => prefix length,
 *     2 => main length,
 *     3 => time,
 *     4 => md5 of main message,
 *     5 => type of message;
 *     6 => is PID;
 * )
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
abstract class parser_base
{

    /**
     * Service Log
     * @var \fan\core\service\log
     */
    protected $oFacade;

    /**
     * Key of dir by Bootstrap
     * @var string
     */
    protected $sLogDirKey = null;

    /**
     * Type of record is available
     * @var boolean
     */
    protected $bIsType = true;
    /**
     * Is serialized
     * @var boolean
     */
    protected $bIsSerialized = true;
    /**
     * Is PID in the log-file
     * @var boolean
     */
    protected $bIsPid = true;

    /**
     * Path to data file
     * @var string
     */
    private $sDataFile = '';

    /**
     * Path to index file
     * @var string
     */
    private $sIndxFile = '';

    /**
     * Size of file
     * @var integer
     */
    protected $nSize = 0;

    /**
     * Index data
     * @var array
     */
    protected $aIndxData = null;

    /**
     * Keys of Unique Index data
     * @var array
     */
    protected $aUniqueKeys = array();

    /**
     * Keys of Similar data
     * @var array
     */
    protected $aSimilarKeys = array();

    /**
     * setFilePath
     */
    public function setFilePath($sVariety, $sFile)
    {
        $sLogDir = $this->sLogDirKey ?
            \bootstrap::getGlobalPath($this->sLogDirKey) :
            \bootstrap::parsePath($this->oFacade->getConfig(array('LOG_DIR', $sVariety)));
        $this->sDataFile = $sLogDir . '/' . $sFile . '.log';
        $this->sIndxFile = $sLogDir . '/' . $sFile . '.i0.php';

        $this->bIsPid = $this->oFacade->getConfig(array('USE_PID', $sVariety), false);

        $this->checkIndex();
    } // function setFilePath

    /**
     * Set Facade
     * @param object $oFacade
     */
    public function setFacade(\fan\core\base\service $oFacade)
    {
        $this->oFacade = $oFacade;
    } // function setFacade

    /**
     * Check Index file
     * @return boolean
     */
    public function checkIndex()
    {
        $sCurSize = is_file($this->sDataFile) ? filesize($this->sDataFile) : 0;
        if ($sCurSize == 0) {
            $this->_removeFile();
            $this->aIndxData = null;
            return false;
        }

        $sCurTime = filemtime($this->sDataFile);
        if (!is_readable($this->sIndxFile) || filemtime($this->sIndxFile) < $sCurTime) {
            $this->_recreateIndex();
        } else {
            $aTmp = include $this->sIndxFile;
            if ($aTmp['size'] == $sCurSize && $aTmp['time'] == $sCurTime) {
                $this->aIndxData = $aTmp['data'];
                $this->nSize     = $sCurSize;
            } else {
                $this->_recreateIndex();
            }
        }
        return true;
    } // function checkIndex


    /**
     * Check is data in this file
     * @param boolean $bIsUnique
     * @return boolean
     */
    public function isData($bReindex = false)
    {
        if ($bReindex) {
            $this->checkIndex();
        }
        return !is_null($this->aIndxData);
    } // function boolean

    /**
     * Get Quantity of elements
     * @param boolean $bIsUnique
     * @return integer
     */
    public function getQtt($bIsUnique = false)
    {
        if (is_null($this->aIndxData)) {
            return null;
        }
        if ($bIsUnique) {
            $this->_setUniqueKeys();
            return count($this->aUniqueKeys);
        }
        return count($this->aIndxData);
    } // function getQtt

    /**
     * Check - Is new elements after last key
     * @param string $sLastKey
     * @param boolean $bIsUnique
     * @return integer - number of next key
     */
    public function checkAfterLast($sLastKey, $bIsUnique = false)
    {
        if (!is_null($this->aIndxData) && !is_null($sLastKey)) {
            if ($bIsUnique) {
                $this->_setUniqueKeys();
                $k = array_search ($sLastKey, $this->aUniqueKeys);
                return $k === false ? 0 : (isset($this->aUniqueKeys[$k + 1]) ? $k + 1 : null);
            }
            return isset($this->aIndxData[$sLastKey + 1]) ? $sLastKey + 1 : null;
        }
        return null;
    } // function checkAfterLast


    /**
     * Get Data Array
     * @param integer $nFirst
     * @param integer $nQtt
     * @param boolean $bIsUnique
     * @return array
     */
    public function getDataArr($nFirst, $nQtt, $bIsUnique = false)
    {
        if (is_null($this->aIndxData)) {
            return null;
        }
        if ($bIsUnique) {
            $this->_setUniqueKeys();
        }

        $aRecords = array();
        $f = fopen($this->sDataFile, 'r');
        for ($i = 0; $i < $nQtt; $i++) {
            $id = $i + $nFirst;
            if ($bIsUnique) {
                if (!isset($this->aUniqueKeys[$id])) {
                    break;
                }
                $id = $this->aUniqueKeys[$id];
            }
            $ind = @$this->aIndxData[$id];
            if (!$ind) {
                break;
            }

            fseek($f, $ind[0] + $ind[1]);
            $aRD = $this->bIsSerialized ? unserialize(stripcslashes(fread($f, $ind[2]))) :
                array(
                    'method'   => '',
                    'request'  => '',
                    'header'   => '',
                    'main_msg' => stripcslashes(fread($f, $ind[2])),
                );
            $aRecords[$i] = array(
                'id'     => $id,
                'attr'   => array(
                    'time'   => $ind[3],
                    'type'   => isset($ind[5]) ? $ind[5] : '',
                ),
                'header' => $aRD['header'],
            );
            if ($this->bIsPid && isset($ind[6])) {
                $aRecords[$i]['attr']['pid'] = $ind[6];
            }
            foreach (array(
                'method',
                'protocol',
                'domain',
                'request',
            ) as $k) {
                if (isset($aRD[$k])) {
                    $aRecords[$i]['attr'][$k] = $aRD[$k];
                }
            }
            foreach (array(
                'data',
                'main_msg',
                'note',
            ) as $k) {
                if (isset($aRD[$k])) {
                    $aRecords[$i][$k] = $aRD[$k];
                }
            }
            if (isset($aRD['trace'])) {
                $aRecords[$i]['trace'] = 1;
            }
        }
        fclose($f);
        return $aRecords;
    } // function getDataArr

    /**
     * Get Trace
     * @param string $sKey
     * @return array
     */
    public function getTrace($sKey)
    {
        if (is_null($this->aIndxData) || !$this->bIsSerialized) {
            return null;
        }
        $aTrace = null;
        $ind = @$this->aIndxData[$sKey];
        if ($ind) {
            $f = fopen($this->sDataFile, 'r');
            fseek($f, $ind[0] + $ind[1]);
            $aRD = unserialize(stripcslashes(fread($f, $ind[2])));
            fclose($f);
            if (isset($aRD['trace'])) {
                $aTrace = $aRD['trace'];
            }
        }
        return $aTrace;
    } // function getTrace

    /**
     * Delete Rows
     * @param boolean $bIsUnique
     */
    public function deleteRows($aKeys, $bIsUnique = false)
    {
        if ($bIsUnique) {
            $this->_setUniqueKeys(true);
        }

        $aOffsets = array();
        foreach ($aKeys as $k1) {
            $sHash = $this->_setOffset($aOffsets, $k1);
            if ($bIsUnique && $sHash) {
                foreach ($this->aSimilarKeys[$sHash] as $k2) {
                    $this->_setOffset($aOffsets, $k2);
                }
            }
        }
        ksort($aOffsets);

        $nStart = 0;
        $aOffsets[$this->nSize] = null;
        rename($this->sDataFile, $this->sDataFile . '.tmp');
        $fw = fopen($this->sDataFile, 'w');
        $fr = fopen($this->sDataFile . '.tmp', 'r');
        foreach ($aOffsets as $k => $v) {
            $nEnd = $k;
            if ($nStart < $nEnd) {
                if (!$this->_dataTransfer($fw, $fr, $nStart, $nEnd)) {
                    // ToDo: take into account error
                    break;
                }
            }
            if ($v) {
                $nStart = $nEnd + $v[0];
                unset($this->aIndxData[$v[1]]);
            }
        }
        fclose($fw);
        fclose($fr);
        unlink($this->sDataFile . '.tmp');
        $this->_recreateIndex();
    } // function deleteRows

    /**
     * Set array of Offsets for delete
     * @param array $aOffsets
     * @param string $k
     * @return string
     */
    protected function _setOffset(&$aOffsets, $k)
    {
        $ind = @$this->aIndxData[$k];
        if ($ind) {
            $aOffsets[$ind[0]] = array(
                $ind[1] + $ind[2] + 1,
                $k,
            );
            return $ind[4];
        }
        return null;
    } // function _setOffset

    /**
     * Recreate index
     */
    protected function _recreateIndex()
    {
        $this->aUniqueKeys  = array();
        if (!filesize($this->sDataFile)) {
            $this->aIndxData = null;
            $this->_removeFile();
            return;
        }
        $aData = array();

        $sStr = '';
        $l = 0;

        $nChunk = $this->oFacade->getConfig('FILE_CHUNK', 8192);
        $f = fopen($this->sDataFile, 'r');
        while (!feof($f)) {
            $sStr .= fread($f, $nChunk);
            $aStr = explode("\n", $sStr);
            for ($i = 0; $i < count($aStr) - 1; $i++) {
                $this->_parceSting($aData, $aStr[$i], $l);
                $l += strlen($aStr[$i]) + 1;
            }
            $sStr = $aStr[$i];
        }
        fclose($f);
        $this->_parceSting($aData, $sStr, $l);

        $this->aIndxData    = $aData;
        $this->_writeIndexFile();
    } // function _recreateIndex

    /**
     * Parce Sting
     */
    protected function _parceSting(&$aData, $sStr, $l)
    {
        if ($sStr) {
            $aArr = explode("\t", $sStr);

            $sMain = end($aArr);
            $len = strlen($sMain);

            $aRow = array(
                0 => $l,
                1 => strlen($sStr) - $len,
                2 => $len,
                3 => $aArr[0],
                4 => md5($sMain),
            );
            if ($this->bIsType) {
                $aRow[5] = $aArr[1];
            }
            if ($this->bIsPid) {
                $aRow[6] = $aArr[$this->bIsType ? 2 : 1];
            }
            $aData[] = $aRow;
        }
    } // function _parceSting

    /**
     * Make and return Unique Index Keys
     * @return array
     */
    protected function _setUniqueKeys()
    {
        if (empty($this->aUniqueKeys)) {
            $this->aUniqueKeys  = array();
            $this->aSimilarKeys = array();
            foreach ($this->aIndxData as $k => $v) {
                $s = $v[4];
                if (!isset($this->aSimilarKeys[$s])) {
                    $this->aUniqueKeys[] = $k;
                    $this->aSimilarKeys[$s] = array($k);
                } else {
                    $this->aSimilarKeys[$s][] = $k;
                }
            }
        }
    } // function _setUniqueKeys

    /**
     * Data transfer from one file to other
     */
    protected function _dataTransfer($fw, $fr, $nStart, $nEnd)
    {
        $nChunk = $this->oFacade->getConfig('FILE_CHUNK', 8192);
        fseek($fr, $nStart);
        while ($nStart < $nEnd) {
            $l = min($nEnd - $nStart, $nChunk);
            if (fwrite($fw, fread($fr, $l)) === false) {
                return false;
            }
            $nStart += $l;
        }
        return true;
    } // function _dataTransfer

    /**
     * Remove Files
     */
    protected function _removeFile()
    {
        if (is_file($this->sDataFile)) {
            unlink($this->sDataFile);
        }
        if (is_file($this->sIndxFile)) {
            unlink($this->sIndxFile);
        }
    } // function _removeFile

    /**
     * Write Index File
     */
    protected function _writeIndexFile()
    {
        $aTmp = array (
            'size' => filesize($this->sDataFile),
            'time' => filemtime($this->sDataFile),
            'data' => $this->aIndxData,
        );
        $this->nSize = $aTmp['size'];
        file_put_contents($this->sIndxFile, '<?php
/*
 * Index data
 */
return ' . var_export($aTmp, true) . ';
?>');
    } // function _writeIndexFile
} // class \fan\core\service\log\parser_base
?>