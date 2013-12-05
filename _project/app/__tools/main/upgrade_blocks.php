<?php namespace app\__tools\main;
/**
 * Upgrade blocks
 * @version 1.0
 */
class upgrade_blocks extends \project\block\common\simple
{
    /**
     * Base Path to upgraded directory
     * @var string
     */
    protected $sBasePath = '';

    /**
     * @var array
     */
    protected $aFileStruct = array(
        'php'  => array(),
        'meta' => array(),
        'tpl'  => array(),
    );

    /**
     * @var array
     */
    protected $aContent = array(
        'php'  => array(),
        'meta' => array(),
        'tpl'  => array(),
    );

    /**
     * @var array
     */
    protected $aChanged = array();

    /**
     * Quantity of Not writeble files
     * @var array
     */
    protected $aNotWr = array(
        'php'  => 0,
        'meta' => 0,
        'tpl'  => 0,
    );

    /**
     * Quantity of Added namespaces
     * @var array
     */
    protected $aNsAdded = array(0, 0, 0);

    /**
     * Quantity of Added namespaces
     * @var array
     */
    protected $aExtSet = array(0, 0, 0);

    /**
     * Service Calls
     * @var array
     */
    protected $aServiceCalls = array(0, 0, 0);

    /**
     * Entity Set
     * @var array
     */
    protected $aEntitySet = array(0, 0, 0);

    /**
     * Direct replacement
     * @var array
     */
    protected $aDirReplace = array(
        'php'  => 0,
        'meta' => 0,
        'tpl'  => 0,
    );

    /**
     * Quantity of Set Final Coment
     * @var array
     */
    protected $aFinalComent = array(0, 0, 0);



    /**
     * Init block
     */
    public function init()
    {
        $this->sBasePath = \bootstrap::parsePath($this->aMeta['src']['path']);


        // Add namespace
        $this->_addNamespase($this->_getFileList('php'), $this->aMeta['src']['ns']);
        $this->view->aNsAdded = $this->aNsAdded;

        // Set Extends
        $this->_setExtends();
        $this->view->aExtSet = $this->aExtSet;

        // Set Service calls
        $this->_setServiceCalls();
        $this->view->aServiceCalls = $this->aServiceCalls;

        // Set Entity
        $this->_setEntityOperations();
        $this->view->aEntitySet = $this->aEntitySet;

        // Set Direct Replacement
        $this->_directReplacement();
        $this->view->aDirReplace = $this->aDirReplace;

        // Set Final Coment
        $this->_setFinalComent($this->_getFileList('php'), '\\' . $this->aMeta['src']['ns'] . '\\');
        $this->view->aFinalComent = $this->aFinalComent;



        // Save changed files
        $aChanged = $this->_saveFiles();

        // Summary info
        $this->view->aChanged = $aChanged;
        $this->view->aNotWr   = $this->aNotWr;
    }

    // ======= Main convert methods ======= \\
    /**
     * Add Namespase
     * @param array $aData
     * @param string $sNsPref
     */
    protected function _addNamespase($aData, $sNsPref)
    {
        $sNS = '<?php namespace ' . $sNsPref;
        foreach ($aData as $k => $v) {
            if (is_array($v)) {
                $this->_addNamespase($v, $sNsPref . '\\' . $k);
            } else {
                $sContent = $this->aContent['php'][$v];
                if (strstr($sContent, 'class ')) {
                    if (preg_match('/^\<\?php\s+namespace\s+\w+/', $sContent)) {
                        $this->aNsAdded[1]++;
                    } else {
                        $nCount = 0;
                        $sContent = preg_replace('/^\<\?php\s*\r?\n/', $sNS . ";\n", $sContent, 1, $nCount);
                        if ($nCount > 0) {
                            $this->aContent['php'][$v] = $sContent;
                            $this->aChanged[$v] = 'php';
                            $this->aNsAdded[0]++;
                        } else {
                            $this->aNsAdded[2]++;
                            trigger_error('Can\'t find first tag in file "' . $v . '".', E_USER_WARNING);
                        }
                    }
                }
            }
        }
    } // function _addNamespase

    /**
     * Set Extends for classes
     */
    protected function _setExtends()
    {
        $aCorr = $this->aMeta['src']['extends'];
        foreach ($this->_getContent('php') as $k => $v) {
            $aMatches = null;
            if (preg_match('/class\s+(\w+)(\s+extends\s+([\w\\\\]+))?\s*\{[\r\n]*/', $v, $aMatches)) {
                if (!empty($aMatches[2])) {
                    if (strstr($aMatches[3], '\\')) {
                        $this->aExtSet[1]++;
                    } else {
                        if (isset($aCorr[$aMatches[3]])) {
                            $this->aContent['php'][$k] = str_replace($aMatches[0], 'class ' . $aMatches[1] . ' extends ' . $aCorr[$aMatches[3]] . "\n{\n", $v);
                            $this->aChanged[$k] = 'php';
                            $this->aExtSet[0]++;
                        } else {
                            $this->aExtSet[2]++;
                            trigger_error('Don\'t know extends "' . $aMatches[3] . '" in file "' . $k . '".', E_USER_WARNING);
                        }
                    }
                }
            } elseif (!empty($aMatches[2])) {
                $this->aExtSet[2]++;
                trigger_error('Can\'t recognize "extends" in file "' . $k . '".', E_USER_WARNING);
            }
        }
    } // function _setExtends

    /**
     * Replace Service Calls
     */
    protected function _setServiceCalls()
    {
        foreach (array('php', 'meta') as $sType) {
            foreach ($this->_getContent($sType) as $k => $v) {
                $aMatches = null;
                if (preg_match_all('/(?<=[\s\n=])service_(\w+)\:\:(instance\(([^\)]*)\))?/', $v, $aMatches, PREG_SET_ORDER)) {
                    $bChanged = false;
                    $bError   = false;
                    foreach ($aMatches as $p) {
                        if (empty($p[2])) {
                            trigger_error('Can\'t convert Service-call "' . $p[0] . '" in file "' . $k . '".', E_USER_WARNING);
                            $bError = true;
                        } else {
                            $sReplacement = 'service(\'' . $p[1] . '\'' . (empty($p[3]) ? '' : ', ' . $p[3]) . ')';
                            $this->aContent[$sType][$k] = $v = str_replace($p[0], $sReplacement, $v);
                            $this->aChanged[$k] = $sType;
                            l(htmlspecialchars($p[0]) . '<br /><br />' . htmlspecialchars($sReplacement), 'Replace service ' . $p[1], $k);
                            $bChanged = true;
                        }
                    }

                    $this->aServiceCalls[$bChanged ? ($bError ? 2 : 0) : 1]++;
                }
            }
        }
    } // function _setServiceCalls

    /**
     * Replace "se" and "le" functions
     */
    protected function _setEntityOperations()
    {
        foreach (array('php', 'meta') as $sType) {
            foreach ($this->_getContent($sType) as $k => $v) {
                $aMatches = null;
                if (preg_match_all('/(?<=[\s\n=])(se|le)\s*\(\s*(?:(\\\'|\")(\w+)\2|[^,\)])\s*(?:\,\s*([^\)]+))?\)([^;]+)?\;/', $v, $aMatches, PREG_SET_ORDER)) {
                    $bChanged = false;
                    $bError   = false;
                    foreach ($aMatches as $p) {
                        $sReplacement = '';
                        if (!empty($p[3]) && substr($p[3], 0, 7) == 'entity_') {
                            $sName = substr($p[3], 7);
                            if ($p[1] == 'le') {
                                $sReplacement = 'gr(\'' . $sName . '\'' . (empty($p[4]) ? '' : ', ' . $p[4]) . ')' . (empty($p[5]) ? '' : $p[5]) . ';';
                            } else {
                                $aMethods = null;
                                if (preg_match('/^\-\>getAggr\(\)\-\>(getEntitiesSimple|getArrayHash|getArrayHashByKey|getArrayColumn|getCountByParam)\s*\(\s*(.*)\s*\)[\r\n\s]*$/s', $p[5], $aMethods)) {
                                    $sArg = empty($aMethods[2]) ? '' : $aMethods[2];
                                    switch ($aMethods[1]) {
                                    case 'getEntitiesSimple':
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowsetByParam(' . $sArg . ');';
                                        break;
                                    case 'getArrayHash':
                                        $aTmp = explode(',', $sArg, 3);
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowsetByParam(' . ltrim($aTmp[2]) . ')->getArrayHash(' . trim($aTmp[0]) . ', ' . trim($aTmp[1]) . ');';
                                        break;
                                    case 'getArrayHashByKey':
                                        $aTmp = explode(',', $sArg, 4);
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowsetByKey(' . trim($aTmp[0]) . ', ' . ltrim($aTmp[3]) . ')->getArrayHash(' . trim($aTmp[1]) . ', ' . trim($aTmp[2]) . ');';
                                        break;
                                    case 'getArrayColumn':
                                        $aTmp = explode(',', $sArg, 2);
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowsetByParam(' . (empty($aTmp[1]) ? '' : $aTmp[1]) . ')->getColumn(' . trim($aTmp[0]) . ');';
                                        break;
                                    case 'getCountByParam':
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowsetByParam(' . $sArg . ')->count();';
                                        break;
                                    }
                                } else if (preg_match('/^\-\>(loadById|loadByParam|loadOrCreate)\s*\(\s*(.*)\s*\)[\r\n\s]*$/s', $p[5], $aMethods)) {
                                    $sArg = empty($aMethods[2]) ? '' : $aMethods[2];
                                    switch ($aMethods[1]) {
                                    case 'loadById':
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowById(' . $sArg . ');';
                                        break;
                                    case 'loadByParam':
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowByParam(' . $sArg . ');';
                                        break;
                                    case 'loadOrCreate':
                                        $sReplacement = 'ge(\'' . $sName . '\')->getRowOrCreate(' . $sArg . ');';
                                        break;
                                    }
                                    //$t = ge($sName)->getRowByParam();
                                } else {
                                    trigger_error('Unrecognized value: ' . $p[5], E_USER_WARNING);
                                }
                            }
                        }

                        if (empty($sReplacement)) {
                            trigger_error('Can\'t convert entity-call "' . $p[0] . '" in file "' . $k . '".', E_USER_WARNING);
                            $bError = true;
                        } else {
                            $this->aContent[$sType][$k] = $v = str_replace($p[0], $sReplacement, $v);
                            $this->aChanged[$k] = $sType;
                            l(htmlspecialchars($p[0]) . '<br /><br />' . htmlspecialchars($sReplacement), 'Replace ' . $p[1], $k);
                            $bChanged = true;
                        }
                    }

                    $this->aEntitySet[$bChanged ? ($bError ? 2 : 0) : 1]++;
                }
            }
        }
    } // function _setEntityOperations

    /**
     * Direct Replacement
     */
    protected function _directReplacement()
    {
        foreach ($this->aMeta['src']['direct_replace'] as $k => $v) {
            if (count($v) > 0) {
                foreach ($this->_getContent($k) as $sPath => $sContent) {
                    $bIsChange = false;
                    foreach ($v as $sPattern => $sReplacement) {
                        $nCount   = 0;
                        $sContent = preg_replace($sPattern, $sReplacement, $sContent, -1, $nCount);
                        if ($nCount > 0) {
                            l(htmlspecialchars($sPattern) . '<br /><br />' . htmlspecialchars($sReplacement), 'Direct replace', $k);
                            $this->aContent[$k][$sPath] = $sContent;
                            $bIsChange = true;
                        }
                    }
                    if ($bIsChange) {
                        $this->aChanged[$sPath] = $k;
                        $this->aDirReplace[$k]++;
                    }
                }
            }
        }
    } // function _directReplacement

    protected function _setFinalComent($aData, $sNsPref)
    {
        $sRegexp = '/\}\s*(\/\/[\w\s\\\\]+)?\s*\r?\n\s*\?\>[\r\n\s]*$/';
        foreach ($aData as $k => $v) {
            if (is_array($v)) {
                $this->_setFinalComent($v, $sNsPref . $k . '\\');
            } else {
                $sContent = $this->aContent['php'][$v];
                if (strstr($sContent, 'class ')) {
                    $aMatches = null;
                    if (preg_match($sRegexp, $sContent, $aMatches)) {
                        if (!empty($aMatches[1]) && strstr($aMatches[1], '\\')) {
                            $this->aFinalComent[1]++;
                        } else {
                            $sContent = str_replace($aMatches[0], '} // class ' . $sNsPref . substr($k, 0, -4) . "\n?>", $sContent);
                            $this->aContent['php'][$v] = $sContent;
                            $this->aChanged[$v] = 'php';
                            $this->aFinalComent[0]++;
                        }
                    } else {
                        $this->aFinalComent[2]++;
                        trigger_error('Can\'t find final tag in file "' . $v . '".', E_USER_WARNING);
                    }
                }
            }
        }
    } // function _setFinalComent


    // --------- Auxiliary methods --------- \\
    /**
     * Get Structured List of Files by type
     * @param type $sType
     * @return type
     */
    protected function _getFileList($sType)
    {
        if (empty($this->aFileStruct[$sType])) {
            $this->_makeFileList($this->aFileStruct[$sType], $sType, $this->sBasePath);
        }
        return $this->aFileStruct[$sType];
    } // function _getFileList

    /**
     * Get Content of Files by type
     * @param type $sType
     * @return type
     */
    protected function _getContent($sType)
    {
        if (empty($this->aContent[$sType])) {
            $this->_makeFileList($this->aFileStruct[$sType], $sType, $this->sBasePath);
        }
        return $this->aContent[$sType];
    } // function _getFileList

    /**
     * Make List File
     * @param array $aDest
     * @param string $sType
     * @param string $sBasePath
     * @return \app\__tools\main\upgrade_blocks
     */
    protected function _makeFileList(&$aDest, $sType, $sBasePath)
    {
        if (is_dir($sBasePath)) {
            foreach (scandir($sBasePath) as $v) {
                if ($v == '.' || $v == '..') {
                    continue;
                }

                $sFullPath = $sBasePath . '/' . $v;
                if (is_dir($sFullPath)) {
                    $aDest[$v] = array();
                    $this->_makeFileList($aDest[$v], $sType, $sFullPath);
                } elseif ($this->_checkType($v, $sType)) {
                    if (!is_writable($sFullPath)) {
                        $this->aNotWr[$sType]++;
                        trigger_error('File "' . $sFullPath . '" is not writable.', E_USER_WARNING);
                    } else {
                        $aDest[$v] = $sFullPath;
                        $this->aContent[$sType][$sFullPath] = file_get_contents($sFullPath);
                    }
                }
            }
        } else {
            trigger_error('Incorrect Base Path: "' . $sBasePath . '".', E_USER_WARNING);
        }
        return $this;
    } // function _makeFileList

    /**
     * Save changed files
     * @return \app\__tools\main\upgrade_blocks
     */
    protected function _saveFiles()
    {
        $aResult = array(
            'php'  => 0,
            'meta' => 0,
            'tpl'  => 0,
        );
        foreach ($this->aChanged as $sPath => $sType) {
            file_put_contents($sPath, $this->aContent[$sType][$sPath]);
            $aResult[$sType]++;
        }
        return $aResult;
    } // function _saveFiles

    /**
     * Check Type of filename
     * @param string $sName
     * @param string $sType
     * @return boolean
     */
    protected function _checkType($sName, $sType)
    {
        if ($sType == 'meta') {
            return substr($sName, -9) == '.meta.php';
        } elseif (substr($sName, -9) != '.meta.php') {
            return substr($sName, -strlen($sType) - 1) == '.' . $sType;
        }
        return false;
    } // function _checkType

} // class \app\__tools\main\upgrade_blocks
?>