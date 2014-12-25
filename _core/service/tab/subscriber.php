<?php namespace fan\core\service\tab;
/**
 * Description of subscriber
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class subscriber extends \fan\core\service\tab\delegate
{
    /**
     * List of Subscriber by block name and by class (with namespace)
     * @var array
     */
    protected $aSubscriber = array('any' => array(), 'name' => array(), 'class' => array());

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Subscribe for events By Block-Name
     * @param \fan\core\block\base $oListener
     * @param string $sEventName
     * @param string $sListenerMethod
     * @return \fan\core\service\tab\delegate\subscriber
     * @throws \fan\project\exception\service\fatal
     */
    public function subscribeForEvent(\fan\core\block\base $oListener, $sEventName, $sListenerMethod = 'eventHandler')
    {
        return $this->_addSubscriber($oListener, $sListenerMethod, 'any', 0, $sEventName);
    } // function subscribeForEvent

    /**
     * Subscribe for events By Block-Name
     * @param \fan\core\block\base $oListener
     * @param string $sBroadcasterName
     * @param string $sEventName
     * @param string $sListenerMethod
     * @return \fan\core\service\tab\delegate\subscriber
     * @throws \fan\project\exception\service\fatal
     */
    public function subscribeByName(\fan\core\block\base $oListener, $sBroadcasterName, $sEventName, $sListenerMethod = 'eventHandler')
    {
        return $this->_addSubscriber($oListener, $sListenerMethod, 'name', $sBroadcasterName, $sEventName);
    } // function subscribeByName

    /**
     * Subscribe for events By Block-Name
     * @param \fan\core\block\base $oListener
     * @param string $sClassName
     * @param string $sEventName
     * @param string $sListenerMethod
     * @return \fan\core\service\tab\delegate\subscriber
     * @throws \fan\project\exception\service\fatal
     */
    public function subscribeByClass(\fan\core\block\base $oListener, $sClassName, $sEventName, $sListenerMethod = 'eventHandler')
    {
        return $this->_addSubscriber($oListener, $sListenerMethod, 'class', trim($sClassName, '\\'), $sEventName);
    } // function subscribeByClass

    /**
     * unSubscribe for events By Block-Name
     * @param \fan\core\block\base $oListener
     * @param string $sBroadcasterName
     * @param string $sEventName
     * @param string $sListenerMethod
     * @return \fan\core\service\tab\delegate\subscriber
     */
    public function unSubscribeByName(\fan\core\block\base $oListener, $sBroadcasterName, $sEventName, $sListenerMethod = 'eventHandler')
    {
        return $this->_removeSubscriber($oListener, $sListenerMethod, 'name', $sBroadcasterName, $sEventName);
    } // function unSubscribeByName

    /**
     * unSubscribe for events By Block-Name
     * @param \fan\core\block\base $oListener
     * @param string $sClassName
     * @param string $sEventName
     * @param string $sListenerMethod
     * @return \fan\core\service\tab\delegate\subscriber
     */
    public function unSubscribeByClass(\fan\core\block\base $oListener, $sClassName, $sEventName, $sListenerMethod = 'eventHandler')
    {
        return $this->_removeSubscriber($oListener, $sListenerMethod, 'class', trim($sClassName, '\\'), $sEventName);
    } // function unSubscribeByClass

    /**
     * Broadcast Event to other blocks
     * @param \fan\core\block\base $oBroadcaster
     * @param string $sEventName
     * @param array $aData
     * @return \fan\core\service\tab\delegate\subscriber
     */
    public function broadcastEvent(\fan\core\block\base $oBroadcaster, $sEventName, $aData = array())
    {
        $aKeys = array(
            'any'   => 0,
            'name'  => $oBroadcaster->getBlockName(),
            'class' => get_class($oBroadcaster),
        );
        foreach ($aKeys as $sType => $sKey) {
            if (isset($this->aSubscriber[$sType][$sKey][$sEventName])) {
                foreach ($this->aSubscriber[$sType][$sKey][$sEventName] as $v) {
                    call_user_func($v, $oBroadcaster, $aData);
                }
            }
        }
        return $this;
    } // function broadcastEvent

    // ======== Private/Protected methods ======== \\

    /**
     * Add new Subscriber
     * @param \fan\core\block\base $oListener
     * @param string $sListenerMethod
     * @param string $sType
     * @param string $sKey
     * @param string $sEventName
     * @return \fan\core\service\tab\delegate\subscriber
     * @throws \fan\project\exception\service\fatal
     */
    protected function _addSubscriber(\fan\core\block\base $oListener, $sListenerMethod, $sType, $sKey, $sEventName)
    {
        if (!method_exists($oListener, $sListenerMethod) || !is_callable(array($oListener, $sListenerMethod))) {
            $this->_makeException('Incorrect method name "' . $sListenerMethod . '" in block "' . $oListener->getBlockName() . '".');
        }
        $this->_removeSubscriber($oListener, $sListenerMethod, $sType, $sKey, $sEventName);

        if (!isset($this->aSubscriber[$sType][$sKey][$sEventName])) {
            $this->aSubscriber[$sType][$sKey][$sEventName] = array();
        }
        $this->aSubscriber[$sType][$sKey][$sEventName][] = array($oListener, $sListenerMethod);
        return $this;
    } // function _addSubscriber

    /**
     * Remove Subscriber
     * @param \fan\core\block\base $oListener
     * @param string $sListenerMethod
     * @param string $sType
     * @param string $sKey
     * @param string $sEventName
     * @return \fan\core\service\tab\delegate\subscriber
     * @throws \fan\project\exception\service\fatal
     */
    protected function _removeSubscriber(\fan\core\block\base $oListener, $sListenerMethod, $sType, $sKey, $sEventName)
    {
        if (isset($this->aSubscriber[$sType][$sKey][$sEventName])) {
            foreach ($this->aSubscriber[$sType][$sKey][$sEventName] as $k => $v) {
                if ($oListener === $v[0] && $sListenerMethod == $v[1]) {
                    unset($this->aSubscriber[$sType][$sKey][$sEventName][$k]);
                    return $this;
                }
            }
        }
        return $this;
    } // function _removeSubscriber

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
} // class \fan\core\service\tab\subscriber
?>