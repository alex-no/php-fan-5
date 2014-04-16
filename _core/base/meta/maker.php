<?php namespace fan\core\base\meta;
/**
 * Meta-Data Maker
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
 * @version of file: 05.02.003 (16.04.2014)
 */
class maker implements \IteratorAggregate
{
    /**
     * Cache of blocks of Meta-data
     * @var array
     */
    protected static $aMetaCache = array();

    /**
     * @var \fan\core\block\base Linked block
     */
    protected $oBlock;

    /**
     * @var string Block Name
     */
    protected $sBlockName;

    /**
     * Array of source Meta-data
     * Keys "folder", "parent", "block" in the Main block
     *   also contain Meta-data where key equal to name of another Blocks
     * Key "main" contain Meta-data from the Main block
     *   and can't be used in the the Main block
     * @var array
     */
    protected $aSource = array(
        'folder'    => array('common' => null, 'own' => null),
        'parent'    => array('common' => null, 'own' => null),
        'block'     => array('common' => null, 'own' => null),
        'container' => array('common' => null, 'own' => null),
        'main'      => array('blockName' => null),
    );

    /**
     * Order of Assemble Meta-data for
     *   "Tab", "Current block", "Other blocks" and "Embeded blocks"
     *   "Other blocks" receive Meta-data from the Main only
     * @var array
     */
    protected $aOrder = array(
        'tab' => array(
            array('folder', 'common'   ),
            array('parent', 'common'   ),
            array('block',  'common'   ),
            array('parent', 'own'      ),
            array('folder', 'own'      ),
            array('block',  'own'      ),
            array('folder', 'blockName'),
        ),
        'current' => array(
            array('folder',    'common'   ),
            array('parent',    'common'   ),
            array('block',     'common'   ),
            array('container', 'common'   ),
            array('parent',    'own'      ),
            array('folder',    'own'      ),
            array('block',     'own'      ),
            array('folder',    'blockName'),
            array('container', 'own'      ),
            array('main',      'blockName'),
        ),
        'other' => array(
            'folder',
            'parent',
            'block',
        ),
        'embeded' => array(
            'parent',
            'block',
        ),
    );

    /**
     * @var \fan\core\base\meta\row
     */
    protected $oRootRow;

    /**
     * Constructor of meta maker
     * @param fan\core\block\base $oBlock
     */
    public function __construct(\fan\core\block\base $oBlock)
    {
        $this->oBlock     = $oBlock;
        $this->sBlockName = $oBlock->getBlockName();

        $aPaths = \fan\project\service\reflector::instance()->getParentPaths($this->oBlock);
        $this->_defineBlockMeta($aPaths);
        $this->_defineFolderMeta($aPaths);
    } // function __construct

    public function __set($sKey, $mValue)
    {
        return $this->oBlock->$sKey = $mValue;
    }

    public function __get($sKey)
    {
        return $this->oBlock->$sKey;
    }

    public function __call($sMethod, $aArguments = array())
    {
        return call_user_func_array(array($this->oBlock, $sMethod), $aArguments);
    }

    final public function getIterator() {
        return $this->getMeta();
    }

    /**
     * Get instance of linked Block
     * @return \fan\core\block\base
     */
    public function getBlock()
    {
        return $this->oBlock;
    } // function getBlock

    /**
     * Set Container Meta
     * @param type $aContainerMeta
     * @return \fan\core\base\meta\maker
     */
    public function setContainerMeta($aContainerMeta)
    {
        $this->_setSource('container', $aContainerMeta);
        return $this;
    } // function setContainerMeta

    /**
     * Set Meta-data assigned for current block in Main-Content block
     * @return \fan\core\base\meta\maker
     */
    public function setMainBlockMeta()
    {
        $oTab = $this->oBlock->getTab();
        $aMainMeta = array(
            $this->sBlockName => $oTab->getBlocksMetaByMain($this->sBlockName)
        );
        $this->_setSource('main', $aMainMeta);
        return $this;
    } // function setMainBlockMeta

    /**
     * Assemble meta-data for Tab
     * @return array
     */
    public function assembleTab()
    {
        $aData = array();
        foreach ($this->getOrder('tab') as $v) {
            $key   = $v[1] == 'blockName' ? $this->sBlockName : $v[1];
            if (isset($this->aSource[$v[0]][$key])) {
                $aData = $this->_mergeMeta($aData, $this->aSource[$v[0]][$key], $v[0]);
            }
        }
        return $aData;
    } // function assembleTab

    /**
     * Assemble meta-data for Block
     * @return \fan\core\base\meta\row
     */
    public function assembleBlock()
    {
        $aData = array();
        foreach ($this->getOrder('current') as $v) {
            $key = $v[1] == 'blockName' ? $this->sBlockName : $v[1];
            if (isset($this->aSource[$v[0]][$key])) {
                $aData = $this->_mergeMeta($aData, $this->aSource[$v[0]][$key], $v[0]);
            }
        }
        $this->oRootRow = new \fan\project\base\meta\row($this, $aData);
        return $this->oRootRow;
    } // function assembleBlock

    /**
     * Assemble Meta-data from the Main to Other Blocks
     * @return array
     */
    public function assembleOther()
    {
        $aData = array();
        foreach ($this->getOrder('other') as $key) {
            $aSource = $this->aSource[$key];
            unset($aSource['common']);
            unset($aSource['own']);
            unset($aSource[$this->sBlockName]);
            $aData = $this->_mergeMeta($aData, $aSource, null);
        }
        return $aData;
    } // function assembleOther

    /**
     * Assemble Meta-data for Embeded Blocks
     * @param string $sBlockName
     * @return array
     */
    public function assembleEmbeded($sBlockName)
    {
        $aData = array();
        foreach ($this->getOrder('embeded') as $key) {
            if (isset($this->aSource[$key][$sBlockName])) {
                $aData = $this->_mergeMeta($aData, $this->aSource[$key][$sBlockName], null);
            }
        }
        return $aData;
    } // function assembleEmbeded

    /**
     * Get all meta-data for embedded blocks
     * @return array
     */
    public function getMixSrcMeta()
    {
        $aFolder    = array('common' => $this->getSource(array('folder', 'common')));
        $aParent    = $this->getSource('parent');
        unset($aParent['own']);
        $aBlock     = $this->getSource('block');
        unset($aBlock['own']);
        $aContainer = array('common' => $this->getSource(array('container', 'common')));
        return array_merge_recursive_alt($aFolder, $aParent, $aBlock, $aContainer);
    } // function getMixSrcMeta

    /**
     * Get Assemble Order of Meta Data
     * @return array
     */
    public function getOrder($sKey)
    {
        if (isset($this->aOrder[$sKey])) {
            return $this->aOrder[$sKey];
        }
        trigger_error('Get Undefined Order of Meta-data "' . $sKey . '" in block "' . $this->sBlockName . '", class "' . get_class($this->oBlock) . '".', E_USER_NOTICE);
        return array();
    } // function getTabOrder

    /**
     * Get Root element of Meta Data
     * @param string|array $mKey
     * @param mixed $mDefault
     * @return \fan\core\base\meta\row
     */
    public function getMeta($mKey = null, $mDefault = null)
    {
        return is_null($mKey) ? $this->oRootRow : $this->oRootRow->get($mKey, $mDefault );
    } // function getMeta

    /**
     * Set Meta Data of Current element
     * @param string|array $mKey
     * @param mixed $mValue
     * @return \fan\core\base\meta\maker
     */
    public function setMeta($mKey, $mValue)
    {
        $this->oRootRow->set($mKey, $mValue);
        return $this;
    } // function setMeta

    /**
     * Get Source of Meta Data
     * @return array
     */
    public function getSource($mKey = null)
    {
        return is_null($mKey) ? $this->aSource : array_get_element($this->aSource, $mKey, false);
    } // function getSourceMeta

    // ============ Protected methods ============ \\
    /**
     * Define Meta of Block-file
     * @param array $aPaths
     */
    protected function _defineBlockMeta($aPaths)
    {
        $aMeta = array();
        foreach ($aPaths as $k => $v) {
            $aMeta[$k] = $this->_loadBlockSource($k, $v);
        }

        // Set Block's Meta
        $this->_setSource('block', array_shift($aMeta));

        // Set Parent Meta
        $aParentMeta = array('common' => array(), 'own' => array());
        foreach (array_reverse($aMeta) as $v) {
            $aParentMeta = $this->_mergeMeta($aParentMeta, $v, 'parent');
        }
        $this->_setSource('parent', $aParentMeta);
    } // function _defineBlockMeta

    /**
     * Define Meta of Folder-file
     * @param array $aPaths
     */
    protected function _defineFolderMeta($aPaths)
    {
        $aPathParts  = pathinfo(array_shift($aPaths));
        $sFolderPath = $aPathParts['dirname'] . '/_folder.meta.php';
        if (file_exists($sFolderPath)) {
            $this->_setSource('folder', include($sFolderPath));
        }
    } // function _defineFolderMeta

    /**
     * Get Source Block Meta
     * @param string $sClass
     * @param string $sPath
     * @return array
     */
    protected function _loadBlockSource($sClass, $sPath)
    {
        if (!array_key_exists($sClass, self::$aMetaCache)) {
            $sMetaPath = substr($sPath, 0, -3) . 'meta.php';
            self::$aMetaCache[$sClass] = file_exists($sMetaPath) ? include($sMetaPath) : null;
        }
        return self::$aMetaCache[$sClass];
    } // function _loadBlockSource

    /**
     * Set source Meta-data
     * @param string $sType
     * @param array $aData
     * @return \fan\core\base\meta\maker
     * @throws \fan\project\exception\block\fatal
     */
    protected function _setSource($sType, $aData)
    {
        if (!key_exists($sType, $this->aSource)) {
            throw new \fan\project\exception\block\fatal($this->oBlock, 'Unknown type "' . $sType . '" of source Meta-data');
        }
        $this->aSource[$sType] = $aData;
        return $this;
    } // function _setSource

    /**
     * Merge Meta-data
     * @param array $aSrcData
     * @param array $aAddData
     * @param string $sType
     * @return array
     */
    protected function _mergeMeta($aSrcData, $aAddData, $sType)
    {
        if (!empty($aAddData) && is_array($aAddData)) {
            foreach ($aAddData as $k => $v) {
                $aSrcData[$k] = isset($aSrcData[$k]) && (is_array($aSrcData[$k]) || is_array($v)) ?
                    $this->_mergeMeta($aSrcData[$k], $v, $sType) :
                    $v; // ToDo: Take into account merging attributes for $sType
            }
        }
        return $aSrcData;
    } // function _mergeMeta

    /**
     * Make Active Meta-data (delayed or in praesenti)
     * @param string $sMethod
     * @param mixed $mArguments
     * @param string|object $mObj
     * @param boolean $bDelayed
     * @return \fan\project\base\meta\delayed|mixed
     */
    protected function _makeActiveMeta($sMethod, $mArguments = array(), $mObj = null, $bDelayed = true)
    {
        if (is_null($mObj)) {
            $mObj = $this->getBlock();
        }
        if (is_null($mArguments)) {
            $mArguments = array();
        } elseif (!is_array($mArguments)) {
            $mArguments = adduceToArray($mArguments);
        }
        $mRet = $bDelayed ? new \fan\project\base\meta\delayed($mObj, $sMethod, $mArguments) : call_user_func_array(array($mObj, $sMethod), $mArguments);
        return $mRet;
    } // function _makeActiveMeta

} // class \fan\core\base\meta\maker
?>