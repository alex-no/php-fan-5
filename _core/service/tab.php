<?php namespace core\service;
use project\exception\service\fatal as fatalException;
/**
 * Class of tab handler
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
 * @version of file: 05.005 (14.01.2014)
 *
 * @method boolean isUseHttps() isUseHttps(array|string $mKey)
 * @method string getCurrentURI() getCurrentURI(boolean $bCorLng, boolean $bAddExt, boolean $bAddQueryStr, boolean $bAddFirstSlash)
 * @method string getURI() getURI(string $sUrn, string $sType, boolean $bUseSid, boolean $bProtocol)
 * @method string addQuery() addQuery(string $sUrn, string $sKey, string $sVal)
 * @method string getDefaultExtension() getDefaultExtension()
 */
class tab extends \core\base\service\single
{
    /**
     *  Marker of URN application prefix
     */
    const URN_AP = '~';

    /**
     * @var array Error transfer flags
     */
    protected static $aErrTransfer = array();

    /**
     * @var array
     */
    protected $aEngine = array();

    /**
     * @var array
     */
    protected $aDelegateRule = array(
        'urlMaker' => array('isUseHttps', 'getCurrentURI', 'getURI', 'addQuery', 'getDefaultExtension'),
    );

    /**
     * @var \core\service\matcher
     */
    protected $oMatcher = null;

    /**
     * View Type definder
     * @var \core\view\definer
     */
    protected $oViewDefiner = null;
    /**
     * Value of View Type
     * @var string
     */
    protected $sViewClass = null;

    /**
     * @var string Current Application Name
     */
    protected $sAppName = null;

    /**
     * Current parsed data
     * @var \core\service\matcher\item\parsed
     */
    protected $aCurrentData = null;

    /**
     * Last parsed data
     * @var \core\service\matcher\item\parsed
     */
    protected $aLastData = null;

    /**
     * @var \core\block\base Main Tab block
     */
    protected $oMainBlock = null;

    /**
     * @var \core\block\base Root Tab block
     */
    protected $oRootBlock = null;

    /**
     * @var \core\block\base Current (performent at this time) Tab block
     */
    protected $oCurrentBlock = null;

    /**
     * Meta-data for this Tab
     * @var array
     */
    protected $aTabMeta = array();

    /**
     * Blocks Meta-data from the Main-block
     * @var array
     */
    protected $aBlocksMeta = array();

    /**
     * Default Meta-data by Tab-configuration according to View-Type
     * @var array
     */
    protected $aDefaultMeta = array();

    /**
     * One-dimensional hash-array for call other blocks
     * @var array
     */
    protected $aBlocks = array();

    /**
     * Two-dimensional array for init data blocks
     * @var array
     */
    protected $aInitOrder = array();

    /**
     * It is made content for show
     * @var string|array
     */
    protected $mContent = '';

    /**
     * Stage of Tab processing
     * @var string
     */
    protected $sStage = null;

    /**
     * @var boolean Enable/Disable Cache
     */
    protected $bCacheEnable = true;
    /**
     * @var number Cache File Time
     */
    protected $nCacheFileTime = null;
    /**
     * @var number Cache Expire Time
     */
    protected $nCacheExpireTime = null;

    /**
     * @var array - times stamps for calculate performance
     */
    protected $aTimesStamp;
    /**
     * @var boolean allow to check performance
     */
    protected $bCheckPerformance;
    /**
     * @var boolean allow to debug operations
     */
    protected $bAllowDebug = null;


    /**
     * Service tab constructor
     * @param boolean $bAllowIni
     */
    protected function __construct($bAllowIni = true)
    {
        parent::__construct($bAllowIni);
        $this->oMatcher = \project\service\matcher::instance();
    } // function __construct

    // ======== Static methods ======== \\

    /**
     *
     * @return string|array
     */
    public static function getContent()
    {
        return \project\service\tab::instance()->_controlTabTransfer()->mContent;
    }

    // ======== Main Interface methods ======== \\

    /**
     * Get Current Content Block
     * @return \core\block\base
     */
    public function getMainBlock()
    {
        return $this->oMainBlock;
    } // function getMainBlock

    /**
     * Get Application Name
     * @return string
     */
    public function getAppName()
    {
        return $this->sAppName;
    } // function getAppName

    /**
     * Get Current Root Block
     * @return \core\block\base
     */
    public function getRootBlock()
    {
        return $this->oRootBlock;
    } // function getRootBlock

    /**
     * Get View Definer
     * @return \core\view\definer
     */
    public function getViewDefiner()
    {
        if(empty($this->oViewDefiner)) {
            $this->oViewDefiner = new \project\view\definer($this->oConfig->get('VIEW_DEFINER', array())->toArray());
        }
        return $this->oViewDefiner;
    } // function getViewDefiner
    /**
     * Get View Class
     * @return string
     */
    public function getViewClass()
    {
        return $this->sViewClass;
    } // function getViewClass

    /**
     * Check is Block "Root" or "Main"
     * @param \core\block\base $oBlock
     * @return array
     */
    public function checkBlockStatus(\core\block\base $oBlock)
    {
        return array($oBlock == $this->oRootBlock, $oBlock == $this->oMainBlock);
    } // function checkBlockStatus

    /**
     * Get Tab Meta from the Main Block
     * @param mixed $mKey
     * @param mixed $mDefautValue
     * @return array
     */
    public function getTabMeta($mKey = null, $mDefautValue = null)
    {
        if (is_null($mKey)) {
            return $this->aTabMeta;
        }
        $mRet = array_get_element($this->aTabMeta, $mKey);
        return is_null($mRet) ? $mDefautValue : $mRet;
    } // function getTabMeta

    /**
     * Get Block Meta from the Main Block
     * @param string $sName
     * @return array
     */
    public function getBlocksMetaByMain($sName)
    {
        return isset($this->aBlocksMeta[$sName]) && is_array($this->aBlocksMeta[$sName]) ? $this->aBlocksMeta[$sName] : array();
    } // function getBlocksMetaByMain

    /**
     * Get Default Meta from Tab-config
     * @return array
     */
    public function getDefaultMeta()
    {
        return $this->aDefaultMeta;
    } // function getDefaultMeta

    /**
     * Check Tab Roles - it is need to call this method ecach time when roles of curent member are changed
     * @param string $sDbOperation
     * @param boolean $bAllowTransfer
     * @return boolean
     */
    public function checkTabRoles($sDbOperation = null, $bAllowTransfer = true)
    {
        $aCond = $this->getTabMeta('roles');
        if ($aCond) {
            do {
                foreach ($aCond as $v) {
                    if (!role($v['condition'])) {
                        $aTransfer = $v;
                        break 2;
                    }
                }
                return true;
            } while (false);

            if ($bAllowTransfer) {
                $oServSes = \project\service\session::instance();
                $sExpire_URL = $oServSes->isExpired() && !$this->getTabMeta('notRedirectByExpire', false) ? $this->oConfig['EXPIRE_URL'] : null;

                if ($sExpire_URL) {
                    transfer_out($sExpire_URL, null, $sDbOperation);
                } elseif (!empty($aTransfer['transfer_sham'])) {
                    transfer_sham($this->getURI($aTransfer['transfer_sham']), null, $sDbOperation);
                } elseif (!empty($aTransfer['transfer_int'])) {
                    transfer_int($this->getURI($aTransfer['transfer_int']), null, $sDbOperation);
                } elseif (!empty($aTransfer['transfer_out'])) {
                    transfer_out($this->getURI($aTransfer['transfer_out']), null, $sDbOperation);
                } else {
                    $this->_parseError403();
                }
            } else {
                return false;
            }
        }
        return true;
    } // function checkTabRoles

    /**
     * Set Current Block
     * @param \core\block\base $oBlock
     * @return \core\service\tab
     */
    public function setCurrentBlock($oBlock)
    {
        $this->oCurrentBlock = $oBlock;
        return $this;
    } // function setCurrentBlock

    /**
     * Get Current Block
     * @return \core\block\base
     */
    public function getCurrentBlock()
    {
        return $this->oCurrentBlock;
    } // function getCurrentBlock

    /**
     * Set Tab Block
     * @param type $oBlock
     * @param type $sName
     * @return \core\service\tab
     * @throws \project\exception\service\fatal
     */
    public function setTabBlock($oBlock, $sName)
    {
        if (isset($this->aBlocks[$sName])) {
            throw new fatalException($this, 'Set dublicate of block with name "' . $sName . '"');
        }
        $this->aBlocks[$sName] = $oBlock;
        $nOrder = $oBlock->getMeta('initOrder', $this->getDefaultInitNum());
        if (!isset($this->aInitOrder[$nOrder])) {
            $this->aInitOrder[$nOrder] = array();
        }
        $this->aInitOrder[$nOrder][] = $oBlock;
        return $this;
    } // function setTabBlock

    /**
     * Get Tab Block object
     * @param sting $sBlockName - name of block
     * @param boolean $bAllowException - Allow Exception if name of block is incorrect
     * @return \core\block\base
     */
    public function getTabBlock($sBlockName, $bAllowException = true)
    {
        if (!isset($this->aBlocks[$sBlockName])) {
            if ($bAllowException) {
                throw new fatalException($this, 'Call undefined block with name "' . $sBlockName . '"');
            }
            return null;
        }
        return $this->aBlocks[$sBlockName];
    } // function getTabBlock

    /**
     * Get Stage of Tab processing
     * @return string
     */
    public function getTabStage()
    {
        return $this->sStage;
    } // function getTabStage

    /**
     * Get Default Init-order number
     * @return numeric
     */
    public function getDefaultInitNum()
    {
        return $this->getConfig('INIT_ORDER_NUM', 1000);
    } // function getDefaultInitNum

    /**
     * Check is Allowed Debug-operations
     * @return boolean
     */
    public function isDebugAllowed()
    {
        if (is_null($this->bAllowDebug)) {
            $aDebugConfig      = \project\service\config::instance()->get('debug');
            $this->bAllowDebug = $aDebugConfig['ENABLED'] && $aDebugConfig['DEBUG_IP'] && !empty($_SERVER['SERVER_ADDR']) && preg_match($aDebugConfig['DEBUG_IP'], $_SERVER['SERVER_ADDR']);
        }
        return $this->bAllowDebug;
    } // function isDebugAllowed

    /**
     * getSubscriber
     * @return subscriber
     */
    public function getSubscriber()
    {
        if (empty($this->aEngine['subscriber'])) {
            $this->aEngine['subscriber'] = $this->_getEngine('subscriber');
        }
        return $this->aEngine['subscriber'];
    } // function getSubscriber

    // ----------- Methods of block cache ------------- \\
    /**
     * @param numeric $nFileTime
     * @param numeric $nExpireTime
     * @return \core\service\tab
     */
    public function setFileTime($nFileTime, $nExpireTime)
    {
        $this->nCacheFileTime = $this->nCacheFileTime ? $nFileTime : max($this->nCacheFileTime, $nFileTime);
        if ($nExpireTime) {
           $this->nCacheExpireTime = $this->nCacheExpireTime ? $nExpireTime : min($this->nCacheExpireTime, $nExpireTime);
        }
        return $this;
    } // function setFileTime

    /**
     * Is allowed Block Cache
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return $this->bCacheEnable;
    } // function getCacheMode
    /**
     * Disable Cache
     * @return \core\service\tab
     */
    public function disableCache()
    {
        $this->bCacheEnable = false;
        return $this;
    } // function disableCache

    // ======== Private/Protected methods ======== \\

    /**
     * Control of Transfer while Tab content making
     * @return string|array
     * @throws fatalException
     */
    protected function _controlTabTransfer()
    {
        $nMaxQttTransfer = $this->getConfig('MAX_QTT_TRANSFER', 10);
        do {
            try {
                // Preparing Tab-property
                $this->_resetProperty()
                     ->_parseError();

                if (empty($this->mContent)) {
                    // Define Main Block. Set Tab meta. Check Tab Roles
                    $this->_setMainBlock($this->aLastData['class'])
                         ->_setTabMeta()
                         ->checkTabRoles();

                    // Set View Class. Set Meta data for Other block by Main block. Set Default Meta by View-type. Make Tab-content.
                    $this->_setViewClass($this->oMainBlock->getViewParserName())
                         ->_setBlocksMetaByMain()
                         ->_setDefaultMeta()
                         ->_makeContent();
                }

                return $this;
            } catch (\core\base\transfer $e) {
                // Catch and make transfer
                $sTransferType = $e->getTransferType();

                $bModify = $sTransferType == 'out' ? null : false;
                $sUri = $this->getURI($e->getRequest(), 'link', $bModify, $bModify);

                // Out transfer
                if ($sTransferType == 'out') {
                    \project\service\header::instance()->sendLocation($sUri);
                }

                $this->oMatcher->setUri($sUri, $e->getHost(), $e->isShiftCurrent());
            }
        } while ($this->oMatcher->getLastIndex() < $nMaxQttTransfer);

        // Exception if quantity of Transfer is more Max
        $sTrList = '';
        foreach ($this->oMatcher->getStack() as $v) {
            $sTrList .= "\n" . $v['source'];
        }
        throw new fatalException($this, 'To many transfers: ' . $sTrList);
    }

    /**
     * Reset Tab property for new content
     * @return \core\service\tab
     */
    protected function _resetProperty()
    {
        $this->sStage       = 'preparing';
        $this->oMainBlock   = null;
        $this->oRootBlock   = null;
        $this->aTabMeta     = array();
        $this->aBlocksMeta  = array();
        $this->aBlocks      = array();
        $this->aInitOrder   = array();
        $this->sViewClass   = null;
        $this->mContent     = '';
        $this->sAppName     = \project\service\application::instance()->getAppName();
        $this->aCurrentData = $this->oMatcher->getCurrentParsedData();
        $this->aLastData    = $this->oMatcher->getLastParsedData();
        return $this;
    } // function _resetProperty

    /**
     * Parse Error of Request to Tab
     * @return \core\service\tab
     */
    public function _parseError()
    {
        $aMainRequest = $this->aLastData['main_request'];
        if (empty($aMainRequest) && (empty(self::$aErrTransfer) || end(self::$aErrTransfer) == 404)) {
            $this->mContent = $this->_parseError404(false);
            return $this;
        }

        if (!empty($aMainRequest)) {
            $sFile = $this->aLastData['file'];
            if (!empty($sFile)) {
                if (file_exists($sFile)) {
                    $sProjectDir = \bootstrap::getLoader()->project;
                    if (substr($sFile, 0, strlen($sProjectDir)) != $sProjectDir) {
                        throw new fatalException($this, 'File "' . $sFile . '" is placed out of Project Dir');
                    }
                    include_once $sFile;
                } else {
                    $this->mContent = $this->_parseError404(true);
                    return $this;
                }
            }
        }
        if (!class_exists($this->aLastData['class'], false)) {
            $this->mContent = $this->_parseError500('Class "' . $this->aLastData['class'] . '" isn\'t found.');
        }
        return $this;
    } // function _parseError

    /**
     * Get Tab Content
     * @return \core\service\tab
     */
    public function _makeContent()
    {
        // Start creating blocks
        $this->sStage = 'creating';
        $nStartTime   = microtime(true);
        $this->_createBlocks();
        $this->aTimesStamp['creating'] = microtime(true) - $nStartTime;

        // Init data blocks
        $this->sStage = 'init';
        $this->_initBlocks($this->_getInitBlocks());

        // Additional init for base blocks
        $this->sStage = 'after_init';
        $this->_runAfterInit();

        $nInitTime = microtime(true);

        // Get output Content
        $this->sStage   = 'output';
        $this->mContent = $this->_getFinalContent($this->oRootBlock);
        $this->_fixPerformance($nStartTime, $nInitTime);

        return $this;
    } // function _makeContent

    /**
     * Set View Class
     * @param string $sViewClass
     * @return \core\service\tab
     */
    protected function _setViewClass($sViewClass)
    {
        if (empty($sViewClass)) {
            throw new fatalException($this, 'Type of view can\'t be empty.');
        }
        $sClass = '\project\view\parser\\' . $sViewClass;
        if (!class_exists($sClass, true)) {
            throw new fatalException($this, 'Class "' . $sClass . '" isn\'t found. Please check your "View definer"');
        }
        $this->sViewClass = $sClass;
        return $this;
    } // function _setViewClass

    /**
     * Create Blocks
     * @return \core\service\tab
     */
    protected function _createBlocks()
    {
        $aRootMeta        = $this->_getRootMeta();
        $this->oRootBlock = $this->_defineRootBlock($aRootMeta);
        return $this;
    } // _createBlocks

    /**
     * Init Blocks
     * @param array $aInitOrderBlock
     * @return \core\service\tab
     */
    protected function _initBlocks($aInitOrderBlock)
    {
        $nPrevTime = microtime(true);
        foreach ($aInitOrderBlock as $aBlocks) {
            foreach ($aBlocks as $oBlock) {
                $this->setCurrentBlock($oBlock);
                $oBlock->setDynamicMeta();
                if (!$oBlock->getRoleCondition()) {
                    if ($oBlock->checkRunInit()) {
                        $oBlock->init();
                    }
                    if (method_exists($oBlock, 'initRequired')) {
                        $oBlock->initRequired();
                    }
                }
                if ($this->bCheckPerformance) {
                    $sBlName = $oBlock->getBlockName();
                    $nCurTime = microtime(true);
                    $this->aTimesStamp['init'][$sBlName] = $nCurTime - $nPrevTime;
                    $nPrevTime = $nCurTime;
                }
            }
        }
        return $this;
    } // _initBlocks
    /**
     * Additional init for "main", "carcass" and "root"
     * @return \core\service\tab
     */
    protected function _runAfterInit()
    {
        $nStartTime = microtime(true);
        foreach (array('main', 'carcass', 'root') as $sBlockName) {
            $oBlock = $this->getTabBlock($sBlockName, false);
            if ($oBlock) {
                $oBlock->runAfterInit();
            }
        }
        $this->aTimesStamp['after_init'] = microtime(true) - $nStartTime;
        return $this;
    } // _runAfterInit

    /**
     * Get Final View Content
     * @param \core\block\base $oRootBlock
     * @return string
     */
    protected function _getFinalContent(\core\block\base $oRootBlock)
    {
        $nDebugMode = $this->_getDebugMode();
        if ($nDebugMode > 0) {
            $sClass = '\project\view\parser\\' . ($nDebugMode == 1 && $this->oMainBlock->getViewType() == 'html' ? 'debug1' : 'debug2');
        } else {
            $sClass = $this->getViewClass();
            if ($this->isDebugAllowed()) {
                $oDebug = \project\service\debug::instance();
                /* @var $oDebug \core\service\debug */
                $oDebug->setExtFiles($oRootBlock, 0);
            }
        }
        /* @var $oViewParser \core\view\parser */
        $oViewParser = new $sClass($this->oMainBlock);
        $oViewParser->startParsing($oRootBlock);
        return $oViewParser->getFinalContent();
    } // function _getFinalContent

    /**
     *
     * @param array $aRootMeta
     * @return \core\block\base
     */
    protected function _defineRootBlock($aRootMeta)
    {
        $aDefaultMeta = $this->getDefaultMeta();
        $sRootClass = $this->getTabMeta(
                'root',
                isset($aDefaultMeta['main']['root']) ? $aDefaultMeta['main']['root'] : null
            );
        $sCarcassClass = $this->getTabMeta(
                'carcass',
                isset($aDefaultMeta['main']['carcass']) ? $aDefaultMeta['main']['carcass'] : null
            );

        $sBlockName = 'root';
        if (empty($sRootClass)) {
            $sRootClass      = $sCarcassClass;
            $sCarcassClass   = null;
            $sBlockName = 'carcass';
        }

        if (empty($sRootClass)) {
            $this->oMainBlock->finishConstruct(null, $aRootMeta, true);
            return $this->oMainBlock;
        }

        if (empty($sCarcassClass)) {
            $aRootMeta['own']['embeddedBlocks']['main'] = '{MAIN}';
        } elseif (!isset($aRootMeta['own']['embeddedBlocks']['carcass'])) {
            $aRootMeta['own']['embeddedBlocks']['carcass'] = $sCarcassClass;
        }

        $oRootBlock = new $sRootClass($sBlockName, $this, null, $aRootMeta, true);
        return $oRootBlock;
    } // function _defineRootBlock

    /**
     * Get Root-Meta
     * @return array
     */
    protected function _getRootMeta()
    {
        $aDefaultMeta = $this->getDefaultMeta();
        if (!empty($aDefaultMeta) && is_object($aDefaultMeta)) {
            $aDefaultMeta = method_exists($aDefaultMeta, 'toArray') ? $aDefaultMeta->toArray() : array();
        }
        return array(
            'own'    => isset($aDefaultMeta['root'])   ? $aDefaultMeta['root']   : array(),
            'common' => isset($aDefaultMeta['common']) ? $aDefaultMeta['common'] : array(),
        );
    } // function _getRootMeta

    /**
     * Get Init Order array
     * @return array
     */
    protected function _getInitBlocks()
    {
        ksort($this->aInitOrder);
        return $this->aInitOrder;
    } // function _getInitBlocks

    /**
     *
     * @return string|array
     */
    protected function _parseError403()
    {
        \project\service\header::instance()->error403(false);
        if (!in_array(403, self::$aErrTransfer)) {
            array_push(self::$aErrTransfer, 403);
            $sUrn = $this->getConfig('error_403', self::URN_AP . '/error403');
            transfer_sham($this->_getPathWithExt($sUrn));
        }

        $oFirstItem = $this->oMatcher->getItem(0);
        $oRunner    = \bootstrap::getRunner();
        return $oRunner->showError(array('urn', $oFirstItem['source']['request']), 'error_403', false);
    } // function _parseError403

    /**
     *
     * @return string|array
     */
    protected function _parseError404($bForse)
    {
        \project\service\header::instance()->error404(false);
        if (empty($bForse) && empty(self::$aErrTransfer)) {
            array_push(self::$aErrTransfer, 404);
            $sUrn = $this->getConfig('error_404', self::URN_AP . '/error404');
            transfer_sham($this->_getPathWithExt($sUrn));
        }

        $oFirstItem = $this->oMatcher->getItem(0);
        $oRunner    = \bootstrap::getRunner();
        return $oRunner->showError(array('urn', $oFirstItem['source']['request']), 'error_404', false);
    } // function _parseError404

    /**
     *
     * @return string|array
     */
    protected function _parseError500($sErrorLog = null)
    {
        if (!empty($sErrorLog)) {
            \project\service\error::instance()->logErrorMessage($sErrorLog, 'Tab Error');
        }
        \project\service\header::instance()->error500(false);
        if (!in_array(500, self::$aErrTransfer)) {
            array_push(self::$aErrTransfer, 500);
            $sUrn = $this->getConfig('error_500', self::URN_AP . '/error500');
            transfer_sham($this->_getPathWithExt($sUrn));
        }

        $oRunner    = \bootstrap::getRunner();
        return $oRunner->showError(array(), 'error_500', false);
    } // function _parseError500

    /**
     *
     * @return string|array
     */
    protected function _getPathWithExt($sUrn, $sDefaultExt = 'html')
    {
        $sExt = $this->getConfig('default_extension', $sDefaultExt);
        return empty($sExt) ? $sUrn : $sUrn . '.' . $sExt;
    } // function _getPathWithExt

    /**
     * Set Current Main Content Block
     * @param string $sClass
     * @return \core\block\base
     */
    protected function _setMainBlock($sClass)
    {
        $this->oMainBlock = new $sClass('main', $this, null, null, false);
        return $this;
    } // function _setMainBlock

    /**
     * Set Meta-data for this Tab
     */
    protected function _setTabMeta()
    {
        $this->aTabMeta = $this->getMainBlock()->metaMaker->assembleTab();
        return $this;
    } // function _setTabMeta

    /**
     * Set Meta-data from the Main Block
     */
    protected function _setBlocksMetaByMain()
    {
        $this->aBlocksMeta = $this->getMainBlock()->metaMaker->assembleOther();
        return $this;
    } // function _setBloksMetaByMain

    /**
     * Set Default Meta-data by View-type from Tab-config
     */
    protected function _setDefaultMeta()
    {
        $this->aDefaultMeta = $this->getConfig(array('DEFAULT_META', $this->oMainBlock->getViewType()), array());
        return $this;
    } // function _setDefaultMeta

    /**
     * Set Debug mode
     * @return number
     */
    public function _getDebugMode()
    {
        $nDebugMode = 0;
        if ($this->isDebugAllowed()) {
            $oSR    = \project\service\request::instance();
            $oSC    = \project\service\cookie::instance();
            $sKey   = $this->getConfig('debug_key', 'debug');
            $nDebug = $oSR->get($sKey, 'PGC', 0);
            if (in_array($nDebug, array(1, 2, 10, 20))) {
                $nDebugMode = (int)substr($nDebug, 0, 1);
                $nDebugG = $oSR->get($sKey, 'PG', 0);
                if ($nDebugG > 9) {
                    $oSC->set($sKey, $nDebugMode);
                } elseif (!is_null($nDebugG)) {
                    $oSC->delete($sKey);
                }
            } elseif (!is_null($nDebug)) {
                $oSC->delete($sKey);
            }
        }
        return $nDebugMode;
    } // function _getDebugMode

    /**
     * Fix Performance
     * @return number
     */
    protected function _fixPerformance($nStartTime, $nInitTime)
    {
        if ($this->bCheckPerformance) {
            $sCurTime = microtime(true);
            $this->aTimesStamp['init_sum'] = sprintf('%01.6f', $nInitTime - $nStartTime - $this->aTimesStamp['consruct']);
            $this->aTimesStamp['add_init'] = sprintf('  %01.6f', $this->aTimesStamp['after_init']);
            $this->aTimesStamp['output']   = sprintf('  %01.6f', $sCurTime  - $nInitTime);
            $this->aTimesStamp['total']    = sprintf('   %01.6f', $sCurTime  - $nStartTime);
            $this->aTimesStamp['creating'] = sprintf('%01.6f', $this->aTimesStamp['consruct']);

            $nLen = 0;
            foreach (array_keys($this->aTimesStamp['init']) as $k) {
                $nLen = max($nLen, strlen($k));
            }

            arsort($this->aTimesStamp['init']);
            foreach ($this->aTimesStamp['init'] as $k => &$v) {
                $v = sprintf(str_repeat(' ', $nLen - strlen($k)) . '%01.6f', $v);
            }

            l('<pre style="font-family: Courier, monospace">' . htmlentities(print_r($this->aTimesStamp, true), ENT_NOQUOTES, 'UTF-8') . '</pre>', 'Estimate Performance by elements');
        }
    } // function _fixPerformance

    // ======== The magic methods ======== \\

} // class \core\service\tab
?>