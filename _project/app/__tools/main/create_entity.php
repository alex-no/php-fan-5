<?php namespace app\__tools\main;
/**
 * create_entity block
 * @version 1.0
 */
class create_entity extends \project\block\form\usual
{

    /**
     * Object of service_database
     * @var \core\service\database
     */
    protected $oDb = null;

    /**
     * Entity dir
     * @var string
     */
    protected $sEttDir = '';

    /**
     * Init block
     */
    public function init()
    {
        $aTableList = array();

        $oReq = service('request');
        /* @var $oReq \core\service\request */
        $sCon  = $oReq->get('connection',   'G');
        $sNsPr = $oReq->get('ns_pref',      'G');
        $sRE   = $oReq->get('table_regexp', 'G');

        if (!empty($sCon) && !empty($sNsPr)) {
            $this->sEttDir = \bootstrap::getLoader()->getPathByNS($sNsPr);
            if (!empty($this->sEttDir)) {
                $this->oDb = service('database', $sCon);
                $this->_parseForm();

                $this->oDb->setResultTypes(MYSQL_NUM);
                $aTmp = $this->oDb->getCol('SHOW TABLES', 0);
                if (!empty($sRE)) {
                    foreach ($aTmp as $v) {
                        if (preg_match($sRE, $v)) {
                            $aTableList[] = $v;
                        }
                    }
                } else {
                    $aTableList = $aTmp;
                }
                $aTableList = array_flip($aTableList);
                ksort($aTableList);
                $sSep  = \core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;

                foreach ($aTableList as $sTableName => &$v) {
                    $sDir = $this->sEttDir . $sSep . $sTableName;
                    if (is_dir($sDir)) {
                        if (!is_file($sDir . $sSep . 'entity.php')) {
                            $v = array('red', 'File of entity-class is not set.');
                        } elseif (!is_file($sDir . $sSep . 'row.php')) {
                            $v = array('yellow', 'File of row-class is not set.');
                        } else {
                            $v = array();
                        }
                    } else {
                        $v = null;
                    }
                }
            }
        }
        /*
        */
        $this->view['CurrentDb']  =  $sCon;
        $this->view['aTableList'] = $aTableList;
    }

    /**
     * On submit
     */
    public function onSubmit()
    {
        $sNsPr = trim(service('request')->get('ns_pref', 'G'), '\\');
        $sSep  = \core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;
        $aTbl = $this->getForm()->getFieldValue('tbl');
        if (!empty($aTbl)) {
            foreach ($aTbl as $sTableName => $v) {
                $sDir = $this->sEttDir . $sSep . $sTableName;
                if (!is_dir($sDir)) {
                    //$aParam = $this->getParamByDb($sTableName);

                    mkdir($sDir);
                    file_put_contents ($sDir . $sSep . 'entity.php' , '<?php namespace ' . $sNsPr . '\\' . $sTableName . ';
/**
 * Entity of `' . $sTableName . '` table
 * @version 1.0
 */
class entity extends \project\base\model\entity
{

    /*
     * ============================== [ Static methods ] ============================== *
     */

    /*
     * ========================== [ Special public methods ] ========================== *
     */

    /*
     * ============================= [ Private/protected methods ] ============================ *
     */

} // class ' . $sNsPr . '\\' . $sTableName . '\entity
?>');

                    file_put_contents ($sDir . $sSep . 'row.php' , '<?php namespace ' . $sNsPr . '\\' . $sTableName . ';
/**
 * Row of `' . $sTableName . '` table' . $this->getMethodList($sTableName) . '
 * @version 1.0
 */
class row extends \project\base\model\row
{

    /*
     * ================ [ Redefined methods AND set/get methods of row-data ] ================ *
     */

    /*
     * ============================== [ Static methods ] ============================== *
     */

    /*
     * ========================== [ Special public methods ] ========================== *
     */

    /*
     * ============================= [ Private/protected methods ] ============================ *
     */

} // class ' . $sNsPr . '\\' . $sTableName . '\row
?>');
                }
            }
        }
    }

    /**
     * Get table parameters by Database
     */
    protected function getMethodList($sTableName)
    {
        $sRet = '';
        foreach ($this->getFields($sTableName) as $v) {
            if (strstr($v['Type'], 'char') || strstr($v['Type'], 'date') || strstr($v['Type'], 'enum')) {
                $sType = 'string';
            } elseif (strstr($v['Type'], 'int')) {
                $sType = 'integer';
            } elseif (strstr($v['Type'], 'float')) {
                $sType = 'float';
            } else {
                $sType = 'mixed';
            }
            $sRet .= "\n" . ' * @method void set_' . $v['Field'] . '()';
            $sRet .= "\n" . ' * @method ' . $sType . ' get_' . $v['Field'] . '()';
        }

        return $sRet;
    }
    /**
     * Get table parameters by Database
     */
    protected function getParamByDb($sTableName)
    {
        $aIndex = array();
        foreach ($this->getFields($sTableName) as $v) {
            if ($v['Key'] == 'PRI') {
                $aIndex[] = $v['Field'];
            }
        }

        $aTmp = $this->oDb->getRow('SHOW CREATE TABLE `' . $sTableName . '`');
        $sCrt = $aTmp['Create Table'];
        $aTopKeys = array();
        if (preg_match_all('/FOREIGN\s+KEY\s*\(\`?(\w+)\`?\)\s*REFERENCES\s+\`?(\w+)\`?\s+\(\`?(\w+)\`?\)/im', $sCrt, $aMatches) && @$aMatches[0]) {
            foreach ($aMatches[1] as $k => $sField) {
                $aTopKeys[$sField] = $aMatches[2][$k];
            }
        }

        return array(
            'primary'  => count($aIndex) < 2 ? @$aIndex[0] : $aIndex,
            'top_keys' => $aTopKeys,
        );
    }

    /**
     * Get list of fields
     * @param string $sTableName
     * @return array
     */
    protected function getFields($sTableName)
    {
        return $this->oDb->getAll('DESCRIBE `' . $sTableName . '`');
    } // function getFields
} // class \app\__tools\main\create_entity
?>