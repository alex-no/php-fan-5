<?php
/**
 * Special PHP-FAN functions
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
 * @version of file: 05.02.009 (23.09.2015)
 */

/**
 * Alternative function for get class name
 * Return NULL if argument is not object (doesn't show warning)
 * @param object $oObject
 * @return string|null
 */
function get_class_alt($oObject)
{
    return is_object($oObject) ? get_class($oObject) : null;
} // function get_class_alt

/**
 * Get class name without namespace
 * @param string|object $oObject
 * @return string|null
 */
function get_class_name($oObject)
{
    if (is_object($oObject)) {
        $oObject = get_class($oObject);
    } else if (!is_string($oObject)) {
        return null;
    }
    $aRet = explode('\\', $oObject);
    return end($aRet);
} // function get_class_name

/**
 *
 * @param string|object $oObject
 * @param integer $nDepth
 * @return string|null
 */
function get_ns_name($oObject, $nDepth = 1)
{
    if (is_object($oObject)) {
        $sName = get_class($oObject);
    } elseif (is_string($oObject)) {
        $sName = $oObject;
    } else {
        return null;
    }
    if ($nDepth < 0 || $nDepth > 40) {
        return null;
    }

    for ($i = 0; $i < $nDepth; $i++) {
        $nPos = strrpos($sName, '\\');
        $sName = $nPos > 0 ? substr($sName, 0, $nPos) : '';
    }
    return $sName;
} // function get_ns_name

/**
 * Alternative check is array or instance of \ArrayAccess
 * @param mixed $aArr
 * @return boolean
 */
function is_array_alt($aArr)
{
    return is_array($aArr) || is_object($aArr) && $aArr instanceof \ArrayAccess;
} // function is_array_alt

/**
 * Explode string and return array with fixed size
 * @param string $sDelimiter
 * @param string $sString
 * @param integer $iSize
 * @return array
 */
function explode_alt($sDelimiter, $sString, $iSize)
{
    $aResult = explode($sDelimiter, $sString, $iSize);
    $iCnt    = count($aResult);
    return $iCnt < $iSize ? array_merge($aResult, array_fill($iCnt, $iSize - $iCnt, null)) : $aResult;
} // function explode_alt

/**
 * Alternative merge recursive function
 * Doesn't convert numeric indexes
 * @param mixed $aArrFirst - first parameter
 * @return array - merged array
 */
function array_merge_recursive_alt($aArrFirst)
{
    if(!is_array_alt($aArrFirst)) {
        if (is_null($aArrFirst)) {
            $aArrFirst = array();
        } else {
            $aArrFirst = array($aArrFirst);
        }
    }
    $numArgs = func_num_args();
    $argList = func_get_args();
    for ($i = 1; $i < $numArgs; $i++) {
        if (!is_null($argList[$i])) {
            $aArrNext = is_array_alt($argList[$i]) ? $argList[$i] : array($argList[$i]);
            foreach ($aArrNext as $k => $v) {
                $aArrFirst[$k] = isset($aArrFirst[$k]) && (is_array_alt($aArrFirst[$k]) || is_array_alt($v)) ?
                    array_merge_recursive_alt($aArrFirst[$k], $v) :
                    $v;
            }
        }
    }
    return $aArrFirst;
} // function array_merge_recursive_alt

/**
 * Check if value is available in array - return value else return Default Value
 * Key can bee scalar or array for multilevel source array
 * @param array|\ArrayAccess $aArr
 * @param mixed $mKey
 * @param mixed $mDefault
 * @return mixed
 */
function array_val($aArr, $mKey, $mDefault = null)
{
    if (is_null($mKey)) {
        return $mDefault;
    }
    if (is_array($aArr) || is_object($aArr) && $aArr instanceof \ArrayAccess) {
        if (is_array($mKey)) {
            if (count($mKey) < 1) {
                return $mDefault;
            }
            $mFirstKey = array_shift($mKey);
            if (count($mKey) > 0) {
                return isset($aArr[$mFirstKey]) ? array_val($aArr[$mFirstKey], $mKey, $mDefault) : $mDefault;
            }
            $mKey = $mFirstKey;
        }
        return isset($aArr[$mKey]) ? $aArr[$mKey] : $mDefault;
    }
    if (!is_null($aArr)) {
        trigger_error('Requested source is not Array', E_USER_NOTICE);
        return null;
    }
    return $mDefault;
} // function array_val

/**
 * Get element of array by mixed key (array or string)
 * @param array $mSource - sourse array
 * @param string|array $mKey - key
 * @param boolean $bMake - make requested element if it isn't exist
 * @return mixed - destination element
 */
function &array_get_element(&$mSource, $mKey, $bMake = null)
{
    /**
     * Anonymous function for check data
     * @param mixed $mDestination - array | instance of \ArrayAccess
     * @param mixed $mKey - array | scalar
     * @param boolean $bMake - Make new element of array if it isn't exists
     * @param boolean $bSave - save source value (into new array) if it isn't array
     * @return boolean
     */
    $fChecker = function(&$mDestination, $mKey, $bMake, $bSave)
    {
        $bIsArray = is_array_alt($mDestination);
        if ((!$bIsArray || !isset($mDestination[$mKey])) && empty($bMake)) {
            return false; // Element not found and can't be made
        }

        if (!$bIsArray) { // Conv to array
            $mDestination = !$bSave || is_null($mDestination) ? array() : array($mDestination);
        }

        if (!isset($mDestination[$mKey])) { // Make element if it is not set
            $mDestination[$mKey] = null;
        }
        return true;
    };

    $mNull = null; // Return if element not found

    if (is_object($mSource) && is_null($bMake)) {
        $bMake = false;
    }

    // If key as array
    if(is_array($mKey)) {
        $bMakeInArray = is_null($bMake) || !empty($bMake);
        $bUseLink     = !is_object($mSource) || $bMakeInArray; // Do not use link for object, because "Magic methods" conflict there
        if ($bUseLink) {
            $aDestination =& $mSource;
        } else {
            $aDestination = $mSource;
        }
        for ($i = 0; isset($mKey[$i]); $i++) {
            if (!$fChecker($aDestination, $mKey[$i], $bMakeInArray, is_null($bMake))) {
                return $mNull;
            }
            if ($bUseLink) {
                $aDestination =& $aDestination[$mKey[$i]];
            } else {
                $aDestination = $aDestination[$mKey[$i]];
            }
        }
        return $aDestination;
    }

    // Else If key as string
    if (!$fChecker($mSource, $mKey, $bMake, true)) {
        return $mNull;
    }
    return $mSource[$mKey];
} // function array_get_element

/**
 * Adduce source value to Array
 * @param mixed $mSrc
 * @return array
 */
function adduceToArray($mSrc)
{
    if (!empty($mSrc)) {
        switch (gettype($mSrc)) {
        case 'object':
            return method_exists($mSrc, 'toArray') ? $mSrc->toArray() : (array)$mSrc;
        case 'array':
            return $mSrc;
        case 'integer':
        case 'double':
        case 'string':
            return array($mSrc);
        }
    }
    return array();
} // function adduceToArray

/**
 * Increase number by power of 10 in 2 (or $nQtt)
 * @param number $nNumber - first parameter
 * @param number $nQtt - quantity signs after point
 * @param boolean $bRoundIt - round result
 * @return number
 */
function increaseNum($nNumber, $nQtt = 2, $bRoundIt = true)
{
    $nTmp = $nNumber * pow(10, $nQtt);
    return $bRoundIt ? round($nTmp) : $nTmp;
} // function increaseNum

/**
 * Decrease number by power of 10 in 2 (or $nQtt)
 *
 * @param number $nNumber - first parameter
 * @param number $nQtt - quantity signs after point
 * @return number
 */
function decreaseNum($nNumber, $nQtt = 2)
{
    return round($nNumber / pow(10, $nQtt), $nQtt);
} // function decreaseNum

/**
 * Get Information About Current Block
 */
function getCurBlockInfo()
{
    if (!class_exists('\fan\project\service\tab', false)) {
        return array(NULL, NULL);
    }
    $oTab   = service('tab');
    $oBlock = $oTab->getCurrentBlock();
    if ($oBlock) {
        $oLoader     = bootstrap::getLoader();
        $oReflection = new \ReflectionClass($oBlock);
        $sPath       = $oReflection->getFileName();
        $sRealPath   = $oLoader->getRealPath($sPath);
        if ($sRealPath) {
            $sPath = str_replace($oLoader->project, '{PROJECT}', $sRealPath);
        }
    } else {
        $sPath = NULL;
    }
    return array($oTab->getTabStage(), $sPath);
} // function getCurBlockInfo

/**
 * Get Instance of Service by name
 * @param string $sServiceName
 * @param array $aArguments
 * @return \fan\core\base\service
 */
function service($sServiceName, $aArguments = array())
{
    $sClass = '\fan\project\service\\' . $sServiceName;
    if (!class_exists($sClass) || !method_exists($sClass, 'instance')) {
        return null;
    }
    return empty($aArguments) ?
            $sClass::instance() :
            call_user_func_array(array($sClass, 'instance'), is_array($aArguments) ? $aArguments : array($aArguments));
} // function service

/**
 * System error handler
 * @param number $nErrNo Error number
 * @param string $sErrMsg Error message
 * @param string $sFileName file name
 * @param number $nLineNum line number
 * @param array $aErrConText An array that points to the active symbol table
 */
function handleError($nErrNo, $sErrMsg, $sFileName, $nLineNum, $aErrConText)
{
    if (!error_reporting()) {
        return;
    }
    if (class_exists('\fan\project\service\error', false) || !\bootstrap::getLoader()->isLoading()) {
        service('error')->handleError($nErrNo, $sErrMsg, $sFileName, $nLineNum, $aErrConText);
    } else {
        \bootstrap::handleError($nErrNo, $sFileName, $sErrMsg, $nErrLine, $aErrContext);
    }
} // function handleError

/**
 * Get Current OR arbitrary user
 * @param mixed $mIdentifyer
 * @param string $sUserSpace
 * @return \fan\core\service\user|null
 */
function getUser($mIdentifyer = null, $sUserSpace = null)
{
    return empty($mIdentifyer) ?
            \fan\project\service\user::getCurrent($sUserSpace) :
            \fan\project\service\user::instance($mIdentifyer, $sUserSpace);
} // function getUser

/**
 * Get instance of specific entity by name
 * @param string $sEntityName Entity Name
 * @param mixed $mCollection Name of Entity Collection
 * @param array $aParam parameter ()
 * @return \fan\core\base\model\entity
 */
function ge($sEntityName, $mCollection = 0, $aParam = array())
{
    return service('entity', $mCollection)->get($sEntityName, $aParam);
} // function ge
/**
 * Load row of entity by name and id.
 * @param string $sEntityName Entity Name
 * @param mixed $mRowId Id of row
 * @param boolean $bIdIsEncrypt
 * @param array $aParam Parameters
 * @return \fan\core\base\model\row
 */
function gr($sEntityName, $mRowId = null, $bIdIsEncrypt = false, $aParam = array())
{
    return service('entity')->get($sEntityName, $aParam)->getRowById($mRowId, $bIdIsEncrypt);
} // function gr
/**
 * Get instance of specific entity.
 * -=!!!!!=- Deprecated - use ge() instead se(). -=!!!!!=-
 * @param string $sEntityName Entity Name
 * @return \fan\core\base\model\entity
 */
function se($sEntityName)
{
    trigger_error('Function "se" is deprecated. Use ge() instead this.', E_USER_NOTICE);
    return ge($sEntityName);
} // function se
/**
 * Load row of entity by name and id.
 * -=!!!!!=- Deprecated - use ge() instead le(). -=!!!!!=-
 * @param string $sEntityName Entity Name
 * @param mixed $mRowId Id of row
 * @return \fan\core\base\model\row
 */
function le($sEntityName, $mRowId = null, $bIdIsEncrypt = false)
{
    trigger_error('Function "le" is deprecated. Use gr() instead this.', E_USER_NOTICE);
    return gr($sEntityName, $mRowId, $bIdIsEncrypt);
} // function le

/**
 * Get Dynamic Meta-data as Scalar value
 * @param string $sKey Data Key
 * @param mixed $mDefaultValue Default Value
 * @return mixed
 */
function dms($sKey, $mDefaultValue = null)
{
    $mScalarValue = service('entity')->getDynamicMetaScalar($sKey);
    return empty($mScalarValue) ? $mDefaultValue : $mScalarValue;
} // function dms

/**
 * Get Dynamic meta-data as array
 * @param string $sKey Data Key
 * @param array $mDefaultValue Default Value
 * @return array
 */
function dma($sKey, $mDefaultValue = array())
{
    $aResult = service('entity')->getDynamicMetaArray($sKey);
    return empty($aResult) ? $mDefaultValue : $aResult;
} // function dma

/**
 * Check allowed and forbidden roles. Roles as condition string
 * @param string $sRoleCondition Role Condition for check
 * @return bolean True if user have Roles to get this object
 */
function role($sRoleCondition)
{
    return service('role')->check($sRoleCondition);
} // function role

/**
 * Outer transfer
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOper Database Operation (commit, rollback)
 */
function transfer_out($sNewUrl, $sNewQueryString = null, $sDbOper = null)
{
    throw new \fan\project\base\transfer\out($sNewUrl, $sNewQueryString, $sDbOper);
} // function transfer_out

/**
 * Internal transfer
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOper Database Operation (commit, rollback)
 */
function transfer_int($sNewUrl, $sNewQueryString = null, $sDbOper = null)
{
    throw new \fan\project\base\transfer\int($sNewUrl, $sNewQueryString, $sDbOper);
} // function transfer_int

/**
 * Sham transfer (do not change current URL)
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOper Database Operation (commit, rollback)
 */
function transfer_sham($sNewUrl, $sNewQueryString = null, $sDbOper = null)
{
    throw new \fan\project\base\transfer\sham($sNewUrl, $sNewQueryString, $sDbOper);
} // function transfer_sham

/**
 * Conver Date from local-format to local MySQL-format
 * @param string $sDate date
 * @param string $sFormat date format
 * @return string
 */
function dateL2M($sDate, $sFormat = 'euro')
{
    return service('date', array($sDate, $sFormat))->get('mysql');
} // function dateL2M

/**
 * Conver Date from MySQL-format to local-format
 * @param string $sDate date
 * @param string $sFormat date format
 * @return string
 */
function dateM2L($sDate, $sFormat = 'euro')
{
    return service('date', array($sDate, 'mysql'))->get($sFormat);
} // function dateM2L

/**
 * Get Message by current language
 * @return string
 */
function msg()
{
    static $sLng = null, $aMsg = array();
    $aArg = func_get_args();

    if (empty($aArg[0])) {
        trigger_error('Error! Call "msg" without arguments.', E_USER_WARNING);
        return '';
    }

    if (count($aArg) > 1) {
        return service('translation')->getCombiMessage($aArg);
    }

    $oSL = service('locale');
    $oST = service('translation');
    if ($oSL->getLanguage() == $sLng && isset($aMsg[$aArg[0]])) {
        return $aMsg[$aArg[0]];
    }

    if ($sLng !== false) {
        if ($oST->getConfig('ALLOW_QUICK_MSG', true)) {
            $sLng = $oSL->getLanguage();
            $aMsg = $oST->getMessageArr($sLng);
        } else {
            $sLng = false;
        }
    }
    return $oST->getMessage($aArg[0]);
} // function msg

/**
 * Get Combi-part Message like msg, but don't save it to message-array
 * @return string
 */
function msgAlt()
{
    $aArg = func_get_args();
    return count($aArg) > 1 ? service('translation')->getCombiMessageAlt($aArg) : $aArg[0];
} // function msg

/**
 * Output dump of variables
 * @param mixed $mData
 * @param string $sTitle
 * @param string $sNote
 * @param number $nDataDepth
 * @param boolean $bIsTrace
 */
function d($mData, $sTitle = 'Custom dump', $sNote = '', $nDataDepth = null, $bIsTrace = true)
{
    service('log')->logData('dump', $mData, $sTitle, $sNote, $nDataDepth, $bIsTrace);
} // function d

/**
 * Logging message
 * @param string $sMessage
 * @param string $sTitle
 * @param string $sNote
 * @param string $sType
 */
function l($sMessage, $sTitle = 'Custom message', $sNote = '', $sType = 'custom')
{
    service('log')->logMessage($sType, $sMessage, $sTitle, $sNote);
} // function l

?>