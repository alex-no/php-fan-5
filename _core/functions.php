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
 * @version of file: 05.001 (29.09.2011)
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
 * @return null
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
 * Altternative (special) merge recursive function
 *
 * @param mixed $aArrFirst - first parameter
 * @return array - merged array
 */
function array_merge_recursive_alt($aArrFirst)
{
    if(!is_array($aArrFirst)) {
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
            $aArrNext = is_array($argList[$i]) ? $argList[$i] : array($argList[$i]);
            foreach ($aArrNext as $k => $v) {
                $aArrFirst[$k] = isset($aArrFirst[$k]) && (is_array($aArrFirst[$k]) || is_array($v)) ?
                    array_merge_recursive_alt($aArrFirst[$k], $v) :
                    $v;
            }
        }
    }
    return $aArrFirst;
} // function array_merge_recursive_alt

/**
 * Check if value is available in array - return value else return Default Value
 * @param array|ArrayAccess $aArr
 * @param mixed $mKey
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
    if (is_object($mSource)) {
        $mSrc  = $mSource;
        $bMake = false;
    } else {
        $mSrc =& $mSource;
    }
    //Anonimouse function for check data
    $fChecker = function(&$aDestination, $mKey, $bMake, $bSave)
    {
        $bIsArray = is_array($aDestination) || is_object($aDestination) && $aDestination instanceof \ArrayAccess;
        if ((!$bIsArray || !isset($aDestination[$mKey])) && empty($bMake)) {
            return false; // Element not found and can't be made
        }

        if (!$bIsArray) { // Conv to array
            $aDestination = !$bSave || is_null($aDestination) ? array() : array($aDestination);
        }

        if (!isset($aDestination[$mKey]) && $bMake) { // Make element if it is not set
            $aDestination[$mKey] = null;
        }
        return true;
    };

    $mNull = null; // Return if element not found

    // If key as array
    if(is_array($mKey)) {
        $bMakeInArray = is_null($bMake) || !empty($bMake);
        if ($bMakeInArray) {
            $aDestination =& $mSrc;
        } else {
            $aDestination = $mSrc;
        }
        for ($i = 0; isset($mKey[$i]); $i++) {
            if (!$fChecker($aDestination, $mKey[$i], $bMakeInArray, is_null($bMake))) {
                return $mNull;
            }
            if ($bMakeInArray) {
                $aDestination =& $aDestination[$mKey[$i]];
            } else {
                $aDestination = $aDestination[$mKey[$i]];
            }
        }
        return $aDestination;
    }

    // If key as string
    if (!$fChecker($mSrc, $mKey, $bMake, true)) {
        return $mNull;
    }
    return $mSrc[$mKey];
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
                return method_exists($mSrc, 'toArray') ? $mSrc->toArray() : array($mSrc);
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
    $nNumber = $nNumber * pow(10, $nQtt);
    return $bRoundIt ? round($nNumber) : $nNumber;
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
    $oTab = class_exists('\project\service\tab', false) ? \project\service\tab::instance() : null;

    if ($oTab) {
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
    }
    return array(NULL, NULL);
} // function getCurBlockInfo

/**
 * Get Instance of Service by name
 * @param string $sServiceName
 * @param array $aArguments
 * @return \core\base\service
 */
function service($sServiceName, $aArguments = array())
{
    $sClass = '\project\service\\' . $sServiceName;
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
    \project\service\error::instance()->handleError($nErrNo, $sErrMsg, $sFileName, $nLineNum, $aErrConText);
} // function handleError

/**
 * Get Current OR arbitrary user
 * @param mixed $mIdentifyer
 * @param string $sUserSpace
 * @return \core\service\user|null
 */
function getUser($mIdentifyer = null, $sUserSpace = null)
{
    return empty($mIdentifyer) ?
            \project\service\user::getCurrent($sUserSpace) :
            \project\service\user::instance($mIdentifyer, $sUserSpace);
} // function getUser

/**
 * Get instance of specific entity by name
 * @param string $sEntityName Entity Name
 * @param mixed $mCollection Name of Entity Collection
 * @param array $aParam parameter ()
 * @return \core\base\model\entity
 */
function ge($sEntityName, $mCollection = 0, $aParam = array())
{
    return \project\service\entity::instance($mCollection)->get($sEntityName, $aParam);
} // function ge
/**
 * Load row of entity by name and id.
 * @param string $sEntityName Entity Name
 * @param mixed $mRowId Id of row
 * @param boolean $bIdIsEncrypt
 * @param array $aParam Parameters
 * @return \core\base\model\row
 */
function gr($sEntityName, $mRowId = null, $bIdIsEncrypt = false, $aParam = array())
{
    return \project\service\entity::instance()->get($sEntityName, $aParam)->getRowById($mRowId, $bIdIsEncrypt);
} // function gr
/**
 * Get instance of specific entity.
 * -=!!!!!=- Deprecated - use ge() instead se(). -=!!!!!=-
 * @param string $sEntityName Entity Name
 * @return \core\base\model\entity
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
 * @return \core\base\model\row
 */
function le($sEntityName, $mRowId = null, $bIdIsEncrypt = false)
{
    trigger_error('Function "le" is deprecated. Use gr() instead this.', E_USER_NOTICE);
    return gr($sEntityName, $mRowId, $bIdIsEncrypt);
} // function le

/**
 * Load Dynamic Meta-data as Scalar value
 * @param string $sKey Data Key
 * @param mixed $mDefaultValue Default Value
 * @return mixed
 */
function dms($sKey, $mDefaultValue = null)
{
    $oEtt = ge('dynamic_meta')->loadOrCreate(array('data_key' => $sKey), array(
        'data_name' => str_replace('_', ' ', $sKey),
        'data_type' => 'scalar',
        'scalar_value' => null,
    ));
    $mScalarValue = $oEtt->get_scalar_value();
    return empty($mScalarValue) ? $mDefaultValue : $mScalarValue;
} // function dms

/**
 * Load Dynamic meta-data as array
 * @param string $sKey Data Key
 * @param array $aDefaultValue Default Value
 * @return array
 */
function dma($sKey, $mDefaultValue = array())
{
    $aRet = ge('dynamic_meta_array')->getRowsetByParam(array('data_key' => $sKey))->getArrayHash('ns_data_key', 'ns_data_value');
    if (empty($aRet)) {
        ge('dynamic_meta')->loadOrCreate(array('data_key' => $sKey), array(
            'data_name' => str_replace('_', ' ', $sKey),
            'data_type' => 'array',
        ));
    }
    return $aRet ? $aRet : $mDefaultValue;
} // function dma

/**
 * Check allowed and forbidden roles. Roles as condition string
 * @param string $sRoleCondition Role Condition for check
 * @return bolean True if user have Roles to get this object
 */
function role($sRoleCondition)
{
    return \project\service\roles::instance()->check($sRoleCondition);
} // function role

/**
 * Outer transfer
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOperation Database Operation (commit, rollback)
 */
function transfer_out($sNewUrl, $sNewQueryString = null, $sDbOperation = null)
{
    throw new \project\base\transfer\out($sNewUrl, $sNewQueryString, $sDbOperation);
} // function transfer_out

/**
 * Internal transfer
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOperation Database Operation (commit, rollback)
 */
function transfer_int($sNewUrl, $sNewQueryString = null, $sDbOperation = null)
{
    throw new \project\base\transfer\int($sNewUrl, $sNewQueryString, $sDbOperation);
} // function transfer_int

/**
 * Sham transfer (do not change current URL)
 * @param string $sNewUrl New Transfer's URL
 * @param string $sNewQueryString New Query String
 * @param string $sDbOperation Database Operation (commit, rollback)
 */
function transfer_sham($sNewUrl, $sNewQueryString = null, $sDbOperation = null)
{
    throw new \project\base\transfer\sham($sNewUrl, $sNewQueryString, $sDbOperation);
} // function transfer_sham

/**
 * Conver Date from local-format to local MySQL-format
 * @param string $sDate date
 * @param string $sFormat date format
 * @param boolean $bFullValidate validate this date
 * @return string
 */
function dateL2M($sDate, $sFormat = 'euro', $bFullValidate = false)
{
    return \project\service\date::instance($sDate, $sFormat)->get('mysql', $bFullValidate);
} // function dateL2M

/**
 * Conver Date from MySQL-format to local-format
 * @param string $sDate date
 * @param string $sFormat date format
 * @param boolean $bFullValidate validate this date
 * @return string
 */
function dateM2L($sDate, $sFormat = 'euro', $bFullValidate = false)
{
    return \project\service\date::instance($sDate, 'mysql')->get($sFormat, $bFullValidate);
} // function dateM2L

/**
 * Get Message by current language
 * @return string
 */
function msg()
{
    static $sLng = null;
    static $aMsg = array();
    $aArg = func_get_args();

    if (empty($aArg[0])) {
        trigger_error('Error! Call "msg" without arguments.', E_USER_WARNING);
        return '';
    }

    if (count($aArg) > 1) {
        return \project\service\translation::getCombiMessage($aArg);
    }

    $oSL = \project\service\locale::instance();
    $oST = \project\service\translation::instance();
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
    return count($aArg) > 1 ? \project\service\translation::getCombiMessage($aArg, NULL, false) : $aArg[0];
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
    \project\service\log::instance()->logData('dump', $mData, $sTitle, $sNote, $nDataDepth, $bIsTrace);
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
    \project\service\log::instance()->logMessage($sType, $sMessage, $sTitle, $sNote);
} // function l

?>