<?php namespace core\block;
/**
 * Base abstract all type of block
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
 * @version of file: 05.002 (17.12.2013)
 *
 * @abstract
 *
 * @property-read string $blockName
 * @property-read \core\block\base $container
 * @property-read \core\base\meta\row $meta
 * @property-read string $namespace
 * @property-read \core\service\request $request
 * @property-read \core\service\session $session
 * @property-read \core\service\tab $tab
 * @property-read \core\view\router $view
 *
 * @method mixed getSessionData() getSessionData(array|string $mKey, mixed $mDefaultValue = null, boolean $bRemoveFromSes = false)
 * @method mixed setSessionData() setSessionData(array|string $mKey, mixed $mValue)
 * @method mixed removeSessionData() removeSessionData(array|string $mKey)
 *
 * @method \core\service\tab\subscriber _subscribeForEvent() subscribeForEvent(string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _subscribeByName() subscribeByName(string $sBroadcasterName, string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _subscribeByClass() subscribeByClass(string $sClassName, string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _unSubscribeForEvent() unSubscribeForEvent(string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _unSubscribeByName() unSubscribeByName(string $sBroadcasterName, string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _unSubscribeByClass() unSubscribeByClass(string $sClassName, string $sEventName, string $sListenerMethod = 'eventHandler')
 * @method \core\service\tab\subscriber _broadcastEvent() broadcastEvent(string $sEventName, array $aData = array())
 */
abstract class base
{
    /**
     * @var string Block's name
     */
    protected $sBlockName = '';
    /**
     * @var boolean Flag: Is current block "Root"
     */
    protected $bIsRoot = false;
    /**
     * @var boolean Flag: Is current block "Main"
     */
    protected $bIsMain = false;

    /**
     * @var \core\service\tab
     */
    protected $oTab;

    /**
     * Meta-Maker instance
     * @var \core\base\meta\maker
     */
    private $oMetaMaker;
    /**
     * @var MetaData
     */
    protected $aMeta;
    /**
     * Flag indicating that the dynamic meta-data are formed
     * @var boolean
     */
    protected $bIsDynMeta = false;

    /**
     * @var array of Role Condition
     */
    protected $aRoleCondition = null;

    /**
     * View data router
     * @var \core\view\router
     */
    private $oView;
    /**
     * Path to template
     * @var string
     */
    private $sTemplate;

    /**
     * Container block
     * @var \core\block\base
     */
    private $oContainer;

    /**
     * @var array of Embedded Blocks
     */
    private $aEmbeddedBlocks = array();

    /**
     * Request-service
     * @var \core\service\request
     */
    private $oRequest;

    /**
     * @var array
     */
    protected $aDelegateRule = array(
        'ordinary' => array(
            'getSessionData'    => array('this', 'getSession', 'get'),
            'setSessionData'    => array('this', 'getSession', 'set'),
            'removeSessionData' => array('this', 'getSession', 'remove'),
        ),
        'identified' => array(
            '_subscribeForEvent'   => array('tab', 'getSubscriber', 'subscribeForEvent'),
            '_subscribeByName'     => array('tab', 'getSubscriber', 'subscribeByName'),
            '_subscribeByClass'    => array('tab', 'getSubscriber', 'subscribeByClass'),
            '_unSubscribeForEvent' => array('tab', 'getSubscriber', 'unSubscribeForEvent'),
            '_unSubscribeByName'   => array('tab', 'getSubscriber', 'unSubscribeByName'),
            '_unSubscribeByClass'  => array('tab', 'getSubscriber', 'unSubscribeByClass'),
            '_broadcastEvent'      => array('tab', 'getSubscriber', 'broadcastEvent'),
        ),
    );

    /**
     * Block constructor
     * @param string $sBlockName Block Name
     * @param \core\service\tab $oTab Service tab
     * @param base $oContainer Block's Container
     * @param array $aContainerMeta - array of Container block Meta
     * @param boolean $bFullConstr - allow to finish Construction of block
     */
    public function __construct($sBlockName = null, \core\service\tab $oTab = null, base $oContainer = null, $aContainerMeta = array(), $bFullConstr = true)
    {
        $this->sBlockName = $sBlockName;
        $this->oTab       = empty($oTab) ? \project\service\tab::instance() : $oTab;
        $this->oRequest   = \project\service\request::instance();

        if (!empty($sBlockName)) {
            $this->oTab->setCurrentBlock($this);
        }

        $this->oMetaMaker = $this->_createMetaMaker();

        if ($bFullConstr) {
            $this->finishConstruct($oContainer, $aContainerMeta);
        }
    } // function __construct

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * Finish Construction of block
     * @param \core\block\base $oContainer
     * @param array $aContainerMeta
     * @param boolean $bAllowSetEmbedded
     */
    public function finishConstruct($oContainer = null, $aContainerMeta = array(), $bAllowSetEmbedded = true)
    {
        if (!empty($oContainer)) {
            $this->oContainer = $oContainer;
        }
        if (!empty($aContainerMeta)) {
            $this->oMetaMaker->setContainerMeta($aContainerMeta);
        }
        $this->oMetaMaker->setContentMeta();

        $this->aMeta = $this->oMetaMaker->assembleBlock();

        $this->_makeDynamicMeta(false);

        $this->oView = $this->_createViewRouter();

        if (!empty($this->sBlockName)) {
            $this->_doRoleOperations();
            if ($this->getRoleCondition()) {
                return;
            }

            // ToDo: Maybe this should be set Tab-block even if the role dosn't permit it
            $this->oTab->setTabBlock($this, $this->sBlockName);
            list($this->bIsRoot, $this->bIsMain) = $this->oTab->checkBlockStatus($this);
        }

        if ($bAllowSetEmbedded) {
            $this->_setEmbeddedBlocks();
        }

        $this->_setTemplate();
        $this->_preparseMeta();
        $this->_postCreate();
    } // function finishConstruct

    /**
     * Init block data
     */
    public function init()
    {
    } // function init

    /**
     * Required init block data
     */
    public function initRequired()
    {
    } // function initRequired



    /**
     * Get Block Name
     * @return string
     */
    public function getBlockName()
    {
        return $this->sBlockName;
    } // function getBlockName

    /**
     * Get Instance of tab
     * @return \core\service\tab
     */
    public function getTab()
    {
        return $this->oTab;
    } // function getTab

    /**
     * Get Instance of Container-block
     * @return \core\block\base
     */
    public function getContainer()
    {
        return $this->oContainer;
    } // function getContainer

    /**
     * Get Meta Maker
     * @return \core\base\meta\maker
     */
    public function getMetaMaker()
    {
        return $this->oMetaMaker;
    } // function getMetaMaker

    /**
     * Get Meta-data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @return \core\base\meta\row
     */
    public function getMeta($mKey = null, $mDefault = null)
    {
        return $this->oMetaMaker->getMeta($mKey, $mDefault);
    } // function getMeta

    /**
     * DEPRECATED (!)
     * Get Meta-data (This method added for compatible with previous version)
     * @param string|array $mKey
     * @return mixed
     */
    public function getMetaVar($mKey = null)
    {
        trigger_error('Use Method "getMeta" instead "getMetaVar".', E_USER_NOTICE);
        return $this->getMeta($mKey);
    } // function getMetaVar

    /**
     * Set value of meta-element
     * @param string|array $mKey - key of var
     * @param mixed $mValue - value
     * @return \core\block\base
     */
    protected function setMeta($mKey, $mValue)
    {
        $this->oMetaMaker->setMeta($mKey, $mValue);
        return $this;
    } // function setMeta

    /**
     * Set value of meta-element (This method added for compatible with previous version)
     * @param mixed $mKey - key of var
     * @param mixed $mValue - value
     */
    protected function setMetaVar($mKey, $mValue)
    {
        trigger_error('Use Method "setMeta" instead "setMetaVar".', E_USER_NOTICE);
        return $this->setMeta($mKey, $mValue);
    } // function setMetaVar

    /**
     * Make Delayed Dynamic Meta-data
     * @return link
     */
    public function makeDelayedMeta(\core\base\meta\row $aMeta)
    {
        foreach ($aMeta as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof \core\base\meta\delayed) {
                    $aMeta[$k] = $v->getValue();
                } elseif ($v instanceof \core\base\meta\row) {
                    $this->makeDelayedMeta($v);
                }
            }
        }
    } // function makeDelayedMeta

    /**
     * get Dynamic Meta-data
     * Allows to create metadata dynamically.
     * Returns generated metadata to add them to the basic meta
     * @param array $aMeta Allow change meta in the parent chain
     * @return array
     */
    public function getDynamicMeta($aMeta)
    {
        return array();
    } // function getDynamicMeta

    /**
     * Set Dynamic Meta-data
     * Create metadata dynamically.
     * This procedure is performed just before the initialization block.
     * Procedure include two basic operations: "makeDelayedMeta" and "makeDynamicMeta"
     */
    public function setDynamicMeta()
    {
        //ToDo: Replace \core\base\meta\delayed to special Flag in \core\base\meta\row
        if(class_exists('\core\base\meta\delayed', false)) {
            $this->makeDelayedMeta($this->aMeta);
        }
        $this->_makeDynamicMeta(true);
        return $this;
    } // function setDynamicMeta

    /**
     * Set cache consider role
     * @param mixed $mRole
     */
    public function setCacheRole($mRole)
    {
        if (is_array($mRole)) {
            foreach ($mRole as $v) {
                $this->setCacheRole($v);
            }
            return $this;
        }
/*
//ToDo: Redesign it
        $aCacheRole = $this->getMeta(array('cache','considerRole'));

        if(!is_array($aCacheRole)) {
            $aCacheRole = array($mRole);
        } elseif (!in_array($mRole, $aCacheRole)) {
            $aCacheRole[] = $mRole;
        }
 */
        return $this;
    } // function setCacheRole

    /**
     * Get Role Conditions if role isn't fit
     * @return boolean
     */
    public function getRoleCondition()
    {
        if (!is_null($this->aRoleCondition)) {
            return empty($this->aRoleCondition) ? null : $this->aRoleCondition;
        }
        $this->aRoleCondition = array();

        $aRoles = $this->getMeta('roles');
        if (!$aRoles) {
            return null;
        }

        foreach ($aRoles as $v) {
            if(!role(@$v['condition'])) {
                $this->aRoleCondition = $v;
                return $v;
            }
        }
        return null;
    } // function getRoleCondition

    /**
     * Get Suffix of Class of View-Parser (withot namespace)
     * This method can be redefined in Main-block
     *   and must return suffix of class-name of View-parcer
     * @return string
     */
    public function getViewParserName()
    {
        $oDefiner = $this->oTab->getViewDefiner();
        return $oDefiner->getViewParserName();
    } // function getViewParserName

    /**
     * Get View Type
     * @return string
     */
    public function getViewType()
    {
        return call_user_func(array($this->oTab->getViewClass(), 'getType'));
    } // function getViewType

    /**
     * Get View Router
     * @return \core\view\router
     */
    public function getView()
    {
        return $this->oView;
    } // function getView

    /**
     * Get All View data
     * @return array
     */
    public function getViewData()
    {
        $this->_preOutput();
        return $this->oView->getAll();
    } // function getViewData

    /**
     * Get Template
     * @return string
     */
    public function getTemplate()
    {
        return $this->sTemplate;
    } // function getTemplate

    /**
     * Set Template file
     * @param string $sTemplatePath
     * @param boolean $bAllowException
     * @return boolean
     * @throws \project\exception\block\fatal
     */
    public function setTemplate($sTemplatePath, $bAllowException = true)
    {
        if (is_file($sTemplatePath)) {
            $this->sTemplate = $sTemplatePath;
            return true;
        } elseif ($bAllowException) {
            throw new \project\exception\block\fatal($this, 'Incorrect template path "' . $sTemplatePath . '"');
        }
        return false;
    } // function setTemplate

    /**
     * Get Request
     * @return \core\service\request
     */
    public function getRequest()
    {
        return $this->oRequest;
    } // function getRequest

    /**
     * Get Namespace of current block
     * @return string
     */
    public function getNamespace()
    {
        $sName = get_class($this);
        $nPos  = strrpos($sName, '\\');
        return $nPos === false ? null : substr($sName, 0, $nPos + 1);
    } // function getNamespace

    /**
     * Get Embedded Blocks
     * @return array
     */
    public function getEmbeddedBlocks()
    {
        return $this->aEmbeddedBlocks;
    } // function getEmbeddedBlocks

    /**
     * Get instance of block's session
     * @return \core\service\session
     */
    public function getSession()
    {
        return \project\service\session::instance(get_class($this), 'block');
    } // function getSession

    /**
     * Check - is allowed to Run Init of block
     * @return boolean
     */
    public function checkRunInit()
    {
        return true;
    } // function checkRunInit

    /**
     * Get debug block information
     */
    public function getDebugInfo()
    {
        $aMetaSourse = $this->oMetaMaker->getSource();
        $aParentPaths  = \project\service\reflector::instance()->getParentPaths($this);
        $sCurrentPath = substr(reset($aParentPaths), 0, -3);

        return array(
            'blockName'      => $this->sBlockName,
            'className'      => get_class($this),
            'templateFile'   => $this->sTemplate,
            'metaFile'       => file_exists($sCurrentPath . 'meta.php') ? $sCurrentPath . 'meta.php' : null,
            'meta'           => $this->aMeta,
            'embeddedBlocks' => $this->aEmbeddedBlocks,
            'parentPaths'    => $aParentPaths,
            'metaSourse'     => $aMetaSourse,

            'folderMeta'     => $aMetaSourse['folder'],
            'fileMeta'       => $aMetaSourse['block'],
            'parentMeta'     => $aMetaSourse['parent'],
            'containerMeta'  => $aMetaSourse['container'],

        );
    } // function getDebugInfo

    // ======== Private/Protected methods ======== \\
    /**
     * Create Meta Maker
     * @return \core\base\meta\maker
     */
    protected function _createMetaMaker()
    {
        return new \project\base\meta\maker($this);
    } // function _createMetaMaker

    /**
     * Create View Router
     * @return \core\view\data
     */
    protected function _createViewRouter()
    {
        return call_user_func(array($this->oTab->getViewClass(), 'getRouter'), $this);
    } // function _createViewRouter

    /**
     * Set View Variable
     * @param string $sKey
     * @param mixed $mVal
     * @return \core\block\base
     */
    protected function _setViewVar($sKey, $mVal)
    {
        $this->oView->set($sKey, $mVal);
        return $this;
    } // function _setViewVar

    /**
     * Previouse parse Meta
     * @return \core\block\base
     */
    protected function _preparseMeta()
    {
        $sViewType = $this->getViewType();
        if ($sViewType == 'html') {
            $oRoot = $this->_getBlock('root', false);
            if ($oRoot) {
                $this->_setRootBlockParameters($oRoot);
            }
        }
        if (in_array($sViewType, array('html', 'loader'))) {
            $aTplVars = $this->getMeta('tplVars');
            if($aTplVars) {
                $this->_setTplVarsByMeta($aTplVars);
            }
        }
        return $this;
    } // function _preparseMeta

    /**
     * Make Dynamic Meta-data
     * @param boolan $bForce - force to make Dynamic Meta-data
     */
    protected function _makeDynamicMeta($bForce)
    {
        if (!$this->bIsDynMeta && ($bForce || $this->getMeta('force_dynamic_meta', false))) {
            $this->bIsDynMeta = true;
            $aDynMeta = $this->getDynamicMeta($this->aMeta);
            if (!empty($aDynMeta)) {
                $this->aMeta = array_merge_recursive_alt(
                    $this->aMeta,
                    $aDynMeta
                );
            }
        }
    } // function _makeDynamicMeta

    /**
     * Redefine role and do other operations connected with roles
     */
    protected function _doRoleOperations()
    {
    } // function _doRoleOperations

    /**
     * Method for redefine in child class
     * Method if run after construct operation
     */
    protected function _postCreate()
    {
    } // function _postCreate

    /**
     * Method for redefine in child class
     * Method if run before output-view operation
     */
    protected function _preOutput()
    {
    } // function _preOutput

    /**
     * Set root-block parameters
     * @param \core\block\root\html $oRoot
     * @param array $aRootKeys
     * @return \core\block\base
     */
    protected function _setRootBlockParameters(\core\block\base $oRoot = null, $aRootKeys = array())
    {
        if (empty($oRoot)) {
            $oRoot  = $this->_getBlock('root');
        }
        if (!$aRootKeys) {
            $aRootKeys = array(
                'externalCss' => 'setExternalCss',
                'embedCss'    => 'setEmbedCss',
                'externalJS'  => 'setExternalJs',
            );
        }

        if(isset($this->aMeta['meta_tag'])) {
            foreach ($this->aMeta['meta_tag'] as $v) {
                $oRoot->setMetaTag($v);
            }
        }
        foreach ($aRootKeys as $k => $m) {
            if(isset($this->aMeta[$k])) {
                $oRoot->$m($this->aMeta[$k]);
            }
        }
        if(isset($this->aMeta['embedJS'])) {
            foreach ($this->aMeta['embedJS'] as $k => $v) {
                $oRoot->setEmbedJs($v, $k);
            }
        }
        return $this;
    } // function _setRootBlockParameters

    /**
     * Set Template Variables By Meta-data
     * @param array $aTplVars
     * @return \core\block\base
     */
    protected function _setTplVarsByMeta($aTplVars)
    {
        foreach ($aTplVars as $k => $v) {
            $this->view->set($k, is_object($v) && method_exists($v, 'toArray') ? $v->toArray() : $v);
        }
        return $this;
    } // function _setTplVarsByMeta

    /**
     * Set Template of block
     * @param string $sTemplateName
     * @throws \core\exception\block\fatal
     */
    protected function _setTemplate($sTemplateName = '')
    {
        $aPaths    = \project\service\reflector::instance()->getParentPaths($this);
        $aSuffixes = $this->_getTplSuffixes();

        // If template-name isn't defined - try to get it from the Meta
        if (!$sTemplateName) {
            $sTemplateName = $this->getMeta('template');
        }
        // If template-name is defined - check and set it
        if ($sTemplateName) {
            // If Template Name is set as full path
            if ($this->_checkTemplate('', \bootstrap::parsePath($sTemplateName), $aSuffixes)) {
                return $this;
            }

            // If Template Name is set as base name (concat with block path)
            reset($aPaths);
            if ($this->_checkTemplate(current($aPaths), $sTemplateName, $aSuffixes)) {
                return $this;
            }

            // Throw exception if defined template-name incorrect
            throw new \project\exception\block\fatal($this, 'Incorrect template name "' . $sTemplateName . '"');
        }

        // Try to find template by block-name
        foreach ($aPaths as $sClass => $sPath) {
            if ($this->_checkTemplate($sPath, get_class_name($sClass), $aSuffixes)) {
                return $this;
            }
        }
        return $this;
    } // function setTemplate

    /**
     * Get array of Suffixes for Template file name
     * @param string $sSeparator
     * @return array
     */
    protected function _getTplSuffixes($sSeparator = '_')
    {
        $aSuffixes = array('');
        if ($this->getMeta('useMultiLanguage')) {
            $sLng = \project\service\locale::instance()->getLanguage();
            if (!empty($sLng)) {
                array_unshift($aSuffixes, $sSeparator . $sLng);
            }
        }
        return $aSuffixes;
    } // function _getTplSuffixes

    /**
     * Check is available Template-file
     * @param string $sBlockPath
     * @param string $sTemplateName
     * @param array $aSuffixes
     * @param string $sExtension
     * @return boolean
     */
    protected function _checkTemplate($sBlockPath, $sTemplateName, $aSuffixes, $sExtension = 'tpl')
    {
        $sBlockPath = empty($sBlockPath) ? '' : dirname($sBlockPath) . '/';
        $sExtension = '.' . $sExtension;

        $nExtLen = -strlen($sExtension);
        if (substr($sTemplateName, $nExtLen) == $sExtension) {
            $sTemplateName = substr($sTemplateName, 0, $nExtLen);
        }

        foreach ($aSuffixes as $v) {
            $sTemplate = $sBlockPath . $sTemplateName . $v . $sExtension;
            if ($this->setTemplate($sTemplate, false)) {
                return true;
            }
        }
        return false;
    } // function _checkTemplate

    /**
     * Set Embedded Blocks
     */
    protected function _setEmbeddedBlocks()
    {
        $aEmbeddedBlocks = $this->getMeta('embeddedBlocks');
        if (!empty($aEmbeddedBlocks)) {
            $aSrcMeta = $this->oMetaMaker->getMixSrcMeta();
            foreach ($aEmbeddedBlocks as $k => $v) {
                if(!is_null($v)) {
                    $aContainerMeta = array(
                        'common' => $aSrcMeta['common'],
                        'own'    => isset($aSrcMeta[$k]) ? $aSrcMeta[$k] : array(),
                    );
                    $sClass = $this->_parseClassName($v);
                    if ($k == 'main') {
                        $this->aEmbeddedBlocks[$k] = $this->oTab->getMainBlock();
                        $this->aEmbeddedBlocks[$k]->finishConstruct($this, $aContainerMeta);
                    } elseif ($sClass) {
                        $this->aEmbeddedBlocks[$k] = new $sClass($k, $this->oTab, $this, $aContainerMeta, true);
                        $this->oTab->setCurrentBlock($this);
                    } else {
                        throw new \project\exception\block\fatal($this, 'Unknown block path "' . $v . '" for Embedded Block');
                    }
                }
            }
        }
    } // function _setEmbeddedBlocks

    /**
     * Get other Blocks
     * @param sting $sBlockName - name of block
     * @param boolean $bAllowException - Allow Exception if name of block is incorrect
     * @return \core\block\base
     */
    protected function _getBlock($sBlockName, $bAllowException = true)
    {
        return $this->oTab->getTabBlock($sBlockName, $bAllowException);
    } // function _getBlock

    /**
     * Get other Blocks
     * @param sting $sBlockName - name of block
     * @param boolean $bAllowException - Allow Exception if name of block is incorrect
     * @return \core\block\base
     */
    protected function _parseClassName($sName)
    {
        if ($sName == '{MAIN}') {
            $oMain = $this->oTab->getMainBlock();
            return empty($oMain) ? null : get_class($oMain);
        }
        $aReplacement = array(
            '{CAPP}' => '\\app\\' . $this->oTab->getAppName(),
            '/'      => '\\',
        );
        $sClass = str_replace(array_keys($aReplacement), array_values($aReplacement), $sName);
        $sClass = substr($sClass, 0, 1) == '\\' ? $sClass : get_ns_name($this) . $sClass;
        return class_exists($sClass) ? $sClass : null;
    } // function _parseClassName

    /**
     * Call Ordinary Delegate
     * @param type $oObject
     * @param type $sMethod
     * @param type $aArgs
     * @return type
     */
    protected function _callOrdinaryDelegate($oObject, $sMethod, $aArgs)
    {
        return call_user_func_array(array($oObject, $sMethod), empty($aArgs) ? array() : $aArgs);
    } // function _callOrdinaryDelegate

    /**
     * Call IdentIfied Delegate
     * @param type $oObject
     * @param type $sMethod
     * @param array $aArgs
     * @return type
     */
    protected function _callIdentifiedDelegate($oObject, $sMethod, $aArgs)
    {
        if (empty($aArgs)) {
            $aArgs = array();
        }
        array_unshift($aArgs, $this);
        return call_user_func_array(array($oObject, $sMethod), $aArgs);
    } // function _callIdentifiedDelegate

    // ======== The magic methods ======== \\

    public function __get($sKey)
    {
        $sMethod = 'get' . ucfirst($sKey);
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod();
        }
        trigger_error('Get Undefined property "' . $sKey . '" in block "' . $this->sBlockName . '", class "' . get_class($this) . '".', E_USER_NOTICE);
        return null;
    }

    /**
     * Call to unset tab method
     * @param string $sMethod method name
     * @param array $aArgs arguments
     * @return mixed Value return by engine
     */
    public function __call($sMethod, $aArgs)
    {
        foreach ($this->aDelegateRule as $sType => $aMethods) {
            foreach ($aMethods as $sName => $aParam) {
                if ($sMethod == $sName) {
                    if (substr($sName, 0, 1) == '_') {
                        // ToDo: Check caller there - must be === $this, else "break";
                    }

                    $sCallMethod = array_pop($aParam);
                    $sObjectName = array_shift($aParam);
                    $oObject = $sObjectName == 'this' ? $this : ($sObjectName == 'tab' ? $this->getTab() : service($sObjectName));
                    foreach ($aParam as $v) {
                        $oObject = call_user_func(array($oObject, $v));
                    }

                    $sDelegateMethod = '_call' . ucfirst($sType) . 'Delegate';
                    return $this->$sDelegateMethod($oObject, $sCallMethod, $aArgs);
                }
            }
        }
        $aTrace = debug_backtrace();
        trigger_error(
                'Incorrect call unknown method "<b>' . $sMethod . '</b>" ' .
                'in block "<b>'    . $this->sBlockName  . '</b>", ' .
                'class "<nobr><b>' . get_class($this)   . '</b></nobr>",<br />' .
                (isset($aTrace[1]['file']) ? 'file "<nobr><b>'  . $aTrace[1]['file'] . '</b></nobr>", ' : 'No file') .
                (isset($aTrace[1]['line']) ? 'line <b>'         . $aTrace[1]['line'] . '</b>.' : ''),
                E_USER_ERROR
        );
    } // function __call
    // ======== Required Interface methods ======== \\
} // class \core\block\base
?>