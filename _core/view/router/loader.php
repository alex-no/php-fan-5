<?php namespace core\view\router;
/**
 * View router of Block for Loader-type
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
 *
 *
 * IMPORTANT: this view has common area for text and json data, but independed for html
 */
class loader extends \core\view\router
{
    /**
     * JSON-keeper
     * @var \core\view\keeper\loader\json
     */
    static protected $oJson = null;
    /**
     * TEXT-keeper
     * @var \core\view\keeper\loader\text
     */
    static protected $oText = null;

    /**
     * Routers array
     * @var array
     */
    protected $aKeepers = array(
        'json' => null,
        'html' => null,
        'text' => null,
    );
    /**
     * Default Routers Key
     * @var string
     */
    protected $sDefaultKey = 'html';

    // ======== Main Interface methods ======== \\
    /**
     * Set special or default view data
     * @param string $sKey
     * @param mixed $mValue
     * @return \core\view\router
     */
    public function set($sKey, $mValue)
    {
        if ($sKey == 'text') {
            $this->_getTextKeeper()->set($sKey, $mValue);
        } else {
            parent::set($sKey, $mValue);
        }
        return $this;
    } // function set

    /**
     * Get JSON-keeper
     * @param string|array $mKey
     * @param mixed $mDefault
     * @param boolean $bLogError
     * @return mixed
     */
    public function getJson($mKey = null, $mDefault = null, $bLogError = true)
    {
        return $this->_getJsonKeeper()->get($mKey, $mDefault, $bLogError);
    } // function getJson

    /**
     * Set JSON-keeper
     * @param string|number $mKey
     * @param mixed $mValue
     * @param boolean $bRewriteExisting - rewrite exists value
     * @return \core\view\router\loader
     */
    public function setJson($mKey, $mValue, $bRewriteExisting = true)
    {
        $this->_getJsonKeeper()->set($mKey, $mValue, $bRewriteExisting, false);
        return $this;
    } // function setJson
    /**
     * Get Text-data
     * @return type
     */
    public function getText()
    {
        return $this->_getTextKeeper()->__toString();
    } // function getText

    /**
     * Set Text-data
     * @param mixed $mValue
     * @param integer $iPosition
     * @return \core\view\router\loader
     */
    public function setText($mValue, $iPosition = 1)
    {
        $this->_getTextKeeper()->set($iPosition, $mValue);
        return $this;
    } // function setText

    /**
     * Is Allowed Full Rewrite data
     * @param \core\view\keeper $oKeeper
     * @return boolean
     */
    public function isFullRewrite(\core\view\keeper $oKeeper)
    {
        foreach ($this->aKeepers as $k => $v) {
            if ($v === $oKeeper) {
                return $k != 'html';
            }
        }
        return false;
    }

    // ======== Private/Protected methods ======== \\
    /**
     *
     * @return \core\view\keeper\loader\json
     */
    protected function _getJsonKeeper()
    {
        if (empty(self::$oJson)) {
            self::$oJson = new \project\view\keeper\loader\json($this);
        } else {
            self::$oJson->addRouter($this);
        }
        return self::$oJson;
    } // function _getJsonKeeper

    protected function _getTextKeeper()
    {
        if (empty(self::$oText)) {
            self::$oText = new \project\view\keeper\loader\text($this);
        } else {
            self::$oText->addRouter($this);
        }
        return self::$oText;
    } // function _getJsonKeeper
} // class \core\view\router\loader
?>