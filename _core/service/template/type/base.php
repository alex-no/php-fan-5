<?php namespace fan\core\service\template\type;
/**
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
abstract class base implements \ArrayAccess
{
    /**
     * @var array Template variables
     */
    protected $aTplVar;

    /**
     * @var \fan\core\block\base Object of block
     */
    protected $oBlock;

    /**
     * @var string Name of block
     */
    protected $sBlockName;

    /**
     * @var string HTML-code
     */
    protected $sFullHTML = '';

    /**
     * @var array Exception keys
     */
    private $aExceptVar = array('this', 'oBlock', 'oTab', 'sAssignTplKey', 'sAssignTplVal', 'sReturnHtmlVal');

    /**
     * @var array Foreach sourse data
     */
    private $aObjectData = array();

    /**
     * Template block constructor
     * @param \fan\core\block\base $oBlock
     */
    public function __construct(\fan\core\block\base $oBlock)
    {
        if (is_object($oBlock) && $oBlock instanceof \fan\core\block\base) {
            $this->oBlock = $oBlock;
            if (method_exists ($oBlock, 'getBlockName')) {
                $this->sBlockName = $oBlock->getBlockName();
            }

            $this->aTplVar['oBlock'] = $oBlock;
            $this->aTplVar['oTab']   = $oBlock->getTab();
        }
    } // function __construct

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    public function __set($sKey, $value)
    {
        return $this->offsetSet($sKey, $value);
    }

    public function __get($sKey)
    {
        return $this->offsetGet($sKey);
    }

    // ======== Required Interface methods ======== \\
    public function offsetSet($sKey, $mValue)
    {
        $sMethod = 'set';
        foreach (explode('_', $sKey) as $v) {
            $sMethod .= ucfirst($v);
        }
        if (method_exists($this, $sMethod)) {
            $this->$sMethod($mValue);
        } else {
            $this->aTplVar[$sKey] = $mValue;
        }
    }

    public function offsetExists($sKey)
    {
        return !empty($this->aTplVar[$sKey]);
    }

    public function offsetUnset($sKey)
    {
        unset($this->aTplVar[$sKey]);
    }

    public function offsetGet($sKey)
    {
        $sMethod = 'get';
        foreach (explode('_', $sKey) as $v) {
            $sMethod .= ucfirst($v);
        }
        return method_exists($this, $sMethod) ? $this->$sMethod() : $this->aTplVar[$sKey];
    }

    // ======== Main Interface methods ======== \\
    /**
     * Get Engine List
     * @return array
     */
    public static function getEngineList()
    {
        return array('main');
    } // function getEngineList

    /**
     * Get Auto-parse data
     * @return array
     */
    public static function getAutoParseTag()
    {
        return array();
    } // function getAutoParseTag

    /**
     * Assign value to parameter of template
     * @param string $sKey Template's Key
     * @param mixed $mValue Assigned value to the parameter
     */
    public function assign($sKey, $mValue)
    {
        $this->checkVars($sKey);
        $this->aTplVar[$sKey] = $mValue;
    } // function assign

    /**
     * Assign value to parameter of template
     * @param string $sKey Template's Key
     * @link mixed $mValue Assigned value to the parameter
     */
    public function assignByRef($sKey, &$mValue)
    {
        $this->checkVars($sKey);
        $this->aTplVar[$sKey] = &$mValue;
    } // function assignByRef


    /**
     * Clear assigned values
     * @param string $sKey Template's Key
     */
    public function clearAssign($sKey)
    {
        $this->checkVars($sKey);
        unset($this->aTplVar[$sKey]);
    } // function clearAssign

    /**
     * Get assigned values
     * @param string $sKey Template's Key
     */
    public function getVars($sKey = null)
    {
        return is_null($sKey) ? @$this->aTplVar : @$this->aTplVar[$sKey];
    } // function getVars

    /**
     * Fetch parced template
     */
    public function fetch()
    {
        $this->sFullHTML = $this->parseHtml();
        return $this->sFullHTML;
    } // function fetch

    // ======== Private/Protected methods ======== \\
    /**
     * Fetch parced template
     */
    abstract protected function parseHtml();

    /**
     * Link For Assign
     * @param string $sKey Template's Key
     */
    protected function &linkForAssign($sKey)
    {
        $this->checkVars($sKey);
        return $this->aTplVar[$sKey];
    } // function linkForAssign

    /**
     * Get number of iteration
     * @param string $sKey Foreach Key
     * @return integer
     */
    protected function getIteration($sKey)
    {
        return $this->aObjectData[$sKey]['iteration'];
    } // function getIteration

    /**
     * Get total count of iteration
     * @param string $sKey Foreach Key
     * @return integer
     */
    protected function getTotal($sKey)
    {
        return count($this->aObjectData[$sKey]['data']);
    } // function getTotal

    /**
     * Check if first iteration
     * @param string $sKey Foreach Key
     * @return boolean
     */
    protected function isFirst($sKey)
    {
        return $this->aObjectData[$sKey]['iteration'] < 2;
    } // function isFirst

    /**
     * Check if last iteration
     * @param string $sKey Foreach Key
     * @return boolean
     */
    protected function isLast($sKey)
    {
        return is_null(@$this->aObjectData[$sKey]['data']) ? false : $this->aObjectData[$sKey]['iteration'] == count($this->aObjectData[$sKey]['data']);
    } // function isLast

    /**
     * Check if last iteration
     * @param string $sKey Foreach Key
     * @return boolean
     */
    protected function isEven($sKey, $bIsBoolean = false)
    {
        $bRet = is_null($this->aObjectData[$sKey]['data']) ? false : $this->aObjectData[$sKey]['iteration']%2 == 0;
	return ($bIsBoolean ? $bRet : ($bRet ? 1 : 0));
    } // function isEven

    /**
     * Check if last iteration
     * @param string $sKey Foreach Key
     * @return string
     */
    protected function makeTagAttr($sAttr, $mData, $sKey = null)
    {
        if(is_object($mData)) {
            $mVal = empty($mData->$sKey) ?
                (method_exists($mData, '__toString') ? $mData->__toString() : '') :
                $mData->$sKey;
        } else {
            $mVal = is_array($mData) ? array_val($mData, is_null($sKey) ? $sAttr : $sKey) : $mData;
        }
        return empty($mVal) ? '' : ' ' . $sAttr . '="' . $mVal . '"';
    } // function makeTagAttr

    /**
     * Set Object Data
     * @param string $sKey Foreach Key
     */
    final protected function setObjectData($sType, $sKey, &$aData = null)
    {
        $this->aObjectData[$sKey] = array('type' => $sType, 'iteration' => 0);
        $this->aObjectData[$sKey]['data'] = &$aData;
    } // function setObjectData

    /**
     *
     * @param string $sKey Foreach Key
     */
    final protected function setIteration($sKey)
    {
        $this->aObjectData[$sKey]['iteration']++;
    } // function setIteration

    /**
     * Check exception keys
     * @param string $sKey Template's Key
     */
    final private function checkVars($sKey)
    {
        if (in_array($sKey, $this->aExceptVar)) {
            throw new \fan\project\exception\template\fatal($this, 'Incorrecn key name "' . $sKey . '". It is reserved name.');
        }
    } // function checkVars
} // class \fan\core\service\template\type\base
?>