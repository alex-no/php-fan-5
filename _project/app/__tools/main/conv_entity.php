<?php namespace fan\app\__tools\main;
/**
 * Covert entity from old to new format
 * @version 05.02.001 (10.03.2014)
 */
class conv_entity extends \fan\project\block\form\usual
{

    /**
     * Destination Directory
     * @var string
     */
    protected $sDstDir = '';

    /**
     * Role name form
     * @var string
     */
    protected $sRoleName = '';

    /**
     * Extendet for class
     * @var array
     */
    protected $aExt = array(
        'entity'  => '\fan\project\base\model\entity',
        'rowset'  => '\fan\project\base\model\rowset',
        'row'     => '\fan\project\base\model\row',
        'request' => '\fan\project\base\model\request',
    );
    /**
     * Init block
     */
    public function init()
    {
        $this->_parseForm();
    } // function init

    /**
     *
     */
    public function onSubmit()
    {
        $oForm = $this->getForm();
        $this->sDstDir = rtrim($oForm->getFieldValue('dest_dir'), '/\\') . '/';
        if (is_dir($this->sDstDir)) {
            $this->sBaseNs = $this->_getNameSpace($this->sDstDir);
            if (empty($this->sBaseNs)) {
                trigger_error('NameSpace is not defined.', E_USER_WARNING);
            } else {
                $sSrcDir = rtrim($oForm->getFieldValue('source_dir'), '/\\') . '/';
                $aFiles  = $this->_makeFileList($sSrcDir, $oForm->getFieldValue('source_mask'));
                foreach ($aFiles as $v) {
                    $sNewDir = $this->sDstDir . $v['table_name'] . '/';
                    if (is_file($sNewDir)) {
                        trigger_error('Such file: "' . $sNewDir . '" already exists.', E_USER_WARNING);
                    } elseif (is_dir($sNewDir)) {
                        trigger_error('Such directory: "' . $sNewDir . '" already exists.', E_USER_NOTICE);
                    } else {
                        mkdir($sNewDir, 0777, true);
                        $sSrcContent = file_get_contents($v['src_file']);

                        $aRowContent = $this->_makeRowContent($sSrcContent, $v['src_file']);
                        $this->_createFile('row', $v['table_name'], $aRowContent);

                        $aEntityContent = $this->_makeEntityContent($sSrcContent);
                        $this->_createFile('entity', $v['table_name'], $aEntityContent);


                        $sAggrFile = $sSrcDir . '../aggr_entities/aggr_' . $v['src_class'];
                        if (is_file($sAggrFile . '.php')) {
                            $aRequestContent = $this->_makeRequestContent(file_get_contents($sAggrFile . '.php'));
                            $this->_createFile('request', $v['table_name'], $aRequestContent);
                            if (is_dir($sAggrFile)) {
                                $sSqlDir = $this->sDstDir . $v['table_name'] . '/sql/';
                                mkdir($sSqlDir, 0766);
                                foreach (scandir($sAggrFile) as $v) {
                                    if ($v == '.' || $v == '..') {
                                        continue;
                                    }
                                    if (!copy($sAggrFile . '/' . $v, $sSqlDir . $v)) {
                                        trigger_error('Can\'t copy SQL-file: "' . $sAggrFile . '/' . $v . '".', E_USER_WARNING);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Make List File
     * @param array $aDest
     * @param string $sType
     * @param string $sBasePath
     * @return array
     */
    protected function _makeFileList($sPath, $sMask)
    {
        $aResult = array();
        if (is_dir($sPath)) {
            foreach (scandir($sPath) as $v) {
                if ($v == '.' || $v == '..') {
                    continue;
                }

                $sFullPath = $sPath . $v;
                $aMatches  = null;
                if (is_file($sFullPath) && preg_match('/^' . $sMask . '\.php$/', $v, $aMatches)) {
                    if (empty($aMatches[1])) {
                        trigger_error('Incorrect mask. Destination class is not set.', E_USER_WARNING);
                        break;
                    }
                    $aResult[] = array(
                        'src_class'  => substr($aMatches[0], 0, -4),
                        'table_name' => $aMatches[1],
                        'src_file'   => $sFullPath
                    );
                }
            }
        } else {
            trigger_error('Incorrect sourse path: "' . $sPath . '".', E_USER_WARNING);
        }
        return $aResult;
    } // function _makeFileList

    /**
     * Get
     */
    protected function _getNameSpace($sPath)
    {
        if(preg_match('/[\/\\\\]model[\/\\\\].*$/', $sPath, $aMatches)) {
            return 'project' . str_replace('/', '\\', $aMatches[0]);
        }
        return null;
    } // function _getNameSpace

    /**
     * Make Row Content
     * @param string $sSrcContent
     * @return array()
     */
    protected function _makeRowContent(&$sSrcContent, $sSrcName)
    {
        $aRowContent = array(
            'set/get' => ' ',
        );
        $aMainMatches = $aMatches = null;
        $sSrcContent = str_replace("\r", '', $sSrcContent);
        preg_match('/^\<\?php\n?\s*(?:\/\*\*(.*?)\s+\*\/)?.*?class\s+entity_.+?\{(.+?)\n\}.*\n\?\>\n?$/si', $sSrcContent, $aMainMatches);

        // ----- comments of dynamic set/get methods ----- \\
        if (!empty($aMainMatches[1])) {
            preg_match_all('/\s+\*\s*\@(?:version|method)\s.+?\n/i', $aMainMatches[1], $aMatches); // PREG_SET_ORDER
            if (!empty($aMatches[0])) {
                $aRowContent['comments'] = implode('', $aMatches[0]);
            }
        }

        if (!empty($aMainMatches[2])) {
            $sSrcContent = trim($aMainMatches[2]);
            $sSrcContent = preg_replace('/\s*\/\*\n\s+\*\s*[-=]{5,}\s*\[.+?\]\s*[-=]{5,}.*?\n\s+\*\//', '', $sSrcContent);
            $sSrcContent = preg_replace('/(?:\s*\/\*\*\n.+?\*\/\n)?\s+public\s+function\s+init\(\).+?\n\s+\}[\s\w\/]*(?:\n|$)/s', '', $sSrcContent);
            $sSrcContent = trim($sSrcContent);
        } else {
            trigger_error('Incorrect file structure "' . $sSrcName . '".', E_USER_WARNING);
            $sSrcContent = '';
        }

        if (empty($sSrcContent)) {
            return $aRowContent;
        }

        // ----- set/get methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*public\sfunction\s(?:s|g)et_.+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $sSrcContent, $aMatches);
        if (!empty($aMatches[0])) {
            $aRowContent['set/get'] = implode('', $aMatches[0]);
            $this->_addSpace($aRowContent['set/get'])->_removeUsed($sSrcContent, $aMatches[0]);
        }

        return $aRowContent;
    } // function _makeRowContent

    /**
     * Make Entity Content
     * @param string $sSrcContent
     * @return array()
     */
    protected function _makeEntityContent(&$sSrcContent)
    {
        $aEntityContent = array(
            'public' => '',
        );
        $aMatches = null;

        // ----- property ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public|protected|private)\s+\$\w+.+?\;\n/s', $sSrcContent, $aMatches);
        if (!empty($aMatches[0])) {
            $aEntityContent['property'] = implode('', $aMatches[0]);

            $this->_addSpace($aEntityContent['property'])->_removeUsed($sSrcContent, $aMatches[0]);
        }

        if (empty($sSrcContent)) {
            return $aEntityContent;
        }

        // ----- static methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public\s+static|static\s+public)\s+function\s.+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $sSrcContent, $aMatches);
        if (!empty($aMatches[0])) {
            $aEntityContent['static'] = implode('', $aMatches[0]);
            $this->_addSpace($aEntityContent['static'])->_removeUsed($sSrcContent, $aMatches[0]);
        }

        if (empty($sSrcContent)) {
            return $aEntityContent;
        }

        // ----- protected methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:protected|private)\s+function\s+(\w+).+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $sSrcContent, $aMatches);
        if (!empty($aMatches[0])) {
            foreach ($aMatches[0] as $k => &$v) {
                $sSrcContent = str_replace($v, '', $sSrcContent);
                $this->_addSpace($v, empty($k));

                if (substr($aMatches[1][$k], 0, 1) != '_') {
                    $v = preg_replace('/\s+function\s+' . $aMatches[1][$k] . '/', ' function _' . $aMatches[1][$k], $sSrcContent);
                }
            }
            $aEntityContent['protected'] = implode('', $aMatches[0]);
            $sSrcContent = trim($sSrcContent);
        }

        // ----- public methods ----- \\
        $aEntityContent['public'] = $sSrcContent;

        return $aEntityContent;
    } // function _makeEntityContent

    /**
     * Make Request Content
     * @param string $sSrcContent
     * @return array()
     */
    protected function _makeRequestContent(&$sSrcContent)
    {
        $aRequestContent = array();
        $sSrcContent = str_replace("\r", '', $sSrcContent);
        $aMainMatches = $aMatches = null;
        if (preg_match('/^\<\?php\n?\s*(?:\/\*\*.*?\s+\*\/)?.*?class\s+aggr_entity_.+?\{(.+?)\n\}.*\n\?\>\n?$/si', $sSrcContent, $aMainMatches)) {
            $sSrcContent = trim($aMainMatches[1]);

            // ----- property ----- \\
            preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public|protected|private)\s+\$\w+.+?\;\n/s', $sSrcContent, $aMatches);
            if (!empty($aMatches[0])) {
                $aRequestContent['property'] = implode('', $aMatches[0]);

                $this->_addSpace($aRequestContent['property'])->_removeUsed($sSrcContent, $aMatches[0]);
            }

            // ----- public methods ----- \\
            $aRequestContent['public'] = $sSrcContent;
        }
        return $aRequestContent;
    } // function _makeRequestContent

    /**
     * Create File of Entity class
     * @param string $sClass
     * @param string $sTableName
     * @param string $sContent
     * @return \app\__tools\main\conv_entity
     */
    protected function _createFile($sClass, $sTableName, $aContent)
    {
        if (empty($aContent)) {
            return $this;
        }
        $sPath = $this->sDstDir . $sTableName . '/' . $sClass . '.php';
        $sNS   = $this->sBaseNs . $sTableName;

        file_put_contents($sPath, '<?php namespace \fan' . $sNS . ';
/**
 * Description of ' . $sClass . '
' . (empty($aContent['comments']) ? '' : ' ' . trim($aContent['comments']) . "\n") . ' *
 * @author Name
 */
class ' . $sClass . ' extends ' . $this->aExt[$sClass] . '
{
' . (empty($aContent['property']) ? '' :  "\n    " . trim($aContent['property']) . "\n") . '
' . (empty($aContent['set/get']) ? '' :  '
    /*
     * ================ [ Redefined methods AND set/get methods of row-data ] ================ *
     */
    ' . trim($aContent['set/get'])) . '
    /*
     * ============================== [ Static methods ] ============================== *
     */
' . (empty($aContent['static']) ? '' :  "\n    " . trim($aContent['static'])) . '
    /*
     * ========================== [ Special public methods ] ========================== *
     */
' . (empty($aContent['public']) ? '' :  "\n    " . trim($aContent['public'])) . '
    /*
     * ============================= [ Private/protected methods ] ============================ *
     */
' . (empty($aContent['protected']) ? '' :  "\n    " . trim($aContent['protected'])) . '
} // class ' . $sNS . '\\' . $sClass . '
?>');
        return $this;
    } // function _createFile

    /**
     * Add Space to Content
     * @param string $sContent
     * @param boolean $bAddCond
     * @return \app\__tools\main\conv_entity
     */
    protected function _addSpace(&$sContent, $bAddCond = true)
    {
        if ($bAddCond && substr($sContent, 0, 4) != '    ') {
            $sContent = '    ' . $sContent;
        }
        return $this;
    } // function _addSpace

    protected function _removeUsed(&$sContent, $aTexts)
    {
        foreach ($aTexts as $v) {
            $sContent = str_replace($v, '', $sContent);
        }
        $sContent = trim($sContent);
        return $this;
    } // function _addSpace

} // class \fan\app\__tools\main\conv_entity
?>