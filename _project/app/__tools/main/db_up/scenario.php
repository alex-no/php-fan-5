<?php namespace fan\app\__tools\main;
/**
 * Parse scenario
 * @version 05.02.001 (10.03.2014)
 */
class scenario
{
    /**
     * @var array
     */
    protected $aScData0;
    /**
     * @var array
     */
    protected $aScData1;

    /**
     * @var number
     */
    protected $nIndex = 0;

    /**
     * @var array
     */
    protected $oDb = null;

    /**
     * Current sql query
     * @var unknown_type
     */
    private $sCurrentQuery = null;

    /**
     * Dump directory path
     * @var string
     */
    private $sDumpdir = null;

    /**
     * Successful operation
     * @var boolean
     */
    private $bSuccess = true;

    /**
     * service's constructor
     * @param array $aConfig Configuration data
     */
    public function __construct($sFile, $sDumpdir = null) {
        $this->aScData0 = file($sFile);
        $this->sDumpdir = $sDumpdir;
        foreach ($this->aScData0 as $sRow) {
            $sRow = trim($sRow);
            if ($sRow != '' && substr($sRow, 0, 1) != '#') {
                $this->aScData[] = $sRow;
            }
        }
    } // function __construct

    public function __call($m, $a) {
        echo '<div>Unknown method <b>' . $m . '</b></div>\n';
    }

    /**
     * Check Success result
     */
    public function isSuccess() {
        return $this->bSuccess;
    } // function isSuccess

    /**
     * Activate service
     */
    public function get_next() {
        if ($this->nIndex >= count($this->aScData)) {
            $ret = array(null,null);
        } else {
            $ret = explode(':', $this->aScData[$this->nIndex], 2);
            $ret[0] = trim(strtolower($ret[0]));
            $ret[1] = trim($ret[1]);
            $this->nIndex++;
        }
        return $ret;
    } // function get_next

    /**
     * Activate service
     */
    public function parse_scenario() {
        do {
            list ($k, $v) = $this->get_next();
            if($k) {
                $sCommand = 'command_' . $k;
                if (!method_exists($this, $sCommand)) {
                    echo '<div class="sc_error"><h2>Error! Unknown scenario\'s command ' . $k . '</h2></div>';
                    $this->bSuccess = false;
                    break;
                }
                try {
                    $this->$sCommand($v);
                } catch (Exception $e) {
                    echo '<div class="sc_error">' . $e->getMessage(), "</div>\n";
                    $this->bSuccess = false;
                    break;
                }
                flush();
            }
        } while($k);
    } // function parse_scenario



//----------------------------------------------
    /**
     * Description command
     */
    public function command_description($name) {
        echo '<h2>' . $name . "</h2>\n";
    } // function command_description

    /**
     * Connect to DB command
     */
    public function command_connect($name) {
        echo '<h3>Connect to DB <i>' . $name . "</i></h3>\n";
        $this->oDb = null;
        $this->oDb = service('database', $name);
        if (!$this->oDb) {
            throw new Exception('<h2>Error! No DB-connection!</h2>');
        }
        service('config')->set('service_database', 'SQL_LNG_CORRECTION', false);
        $this->check_sql_error();
    } // function command_connect

    /**
     * Commit DB command
     */
    public function command_commit() {
        echo "<h3>Commit data.</h3>\n";
        $this->oDb->commit();
    } // function command_commit

    /**
     * Clear DB tables command
     */
    public function command_clear_tables() {
        echo "<h3>Clear all database tables.</h3>\n";

        $aTableList = $this->get_table_list('Deleted tables');

        $this->clear_fk($aTableList);
        foreach ($aTableList as $sTable) {
            $this->execute('DROP TABLE `' . $sTable . '`');
        }
        $this->oDb->commit();
    } // function command_clear_tables

    /**
     * Clear Foreign keys command
     */
    public function command_clear_fk() {
        echo "<h3>Clear Foreign Keys in all tables.</h3>\n";

        $aTableList = $this->get_table_list('Clear FK in tables');

        $this->clear_fk($aTableList);
    } // function command_clear_fk

    /**
     * Command for run sql-request from file
     */
    public function command_sql_file($file) {
        echo '<h3>File <i>' . $file . "</i></h3>\n";
        if(!file_exists($this->sDumpdir . $file)) {
            throw new Exception('<h2>Error! No file <i>' . $file . '</i></h2>');
        }
        $sFile = trim(file_get_contents($this->sDumpdir . $file));
        $sFile = str_replace("\r\n", "\n", $sFile);
        $eof = false;
        $k=1;
        while ($sFile != '' && $sFile != '--') {
            if (substr($sFile, 0, 3) == '-- ') {
                $sFile = (string)strstr($sFile, "\n");
            } else {
                $nPos = strpos($sFile, ";\n");
                if($nPos === false) {
                    $nPos = substr($sFile,-1) == ';' ? strlen($sFile) - 1 : strlen($sFile);
                    $eof = true;
                } elseif($nPos == 0){
                    $sFile = substr($sFile,1);
                    continue;
                }
                echo '<div>SQL-request #', $k++, '</div>';
                if($k%10 == 0) {
                    flush();
                }

                $this->execute(substr($sFile,0,$nPos));
                $sFile = $eof ? '' : substr($sFile, $nPos+1);
            }
            $sFile = trim($sFile);
        }

    } // function command_sql_file

    /**
     * Command for run sql-query from scenario
     */
    public function command_sql_query($query) {
        echo "<h3>Scenario's query</h3>\n";
        $this->execute($query);
    } // function command_sql_query

//----------------------------------------------
    /**
     * Execute SQL and check error
     */
    protected function execute($query, $param = array()) {
        $this->sCurrentQuery = $query;
        $this->oDb->execute($query, $param);
        $this->check_sql_error();
    } // function execute

    /**
     * Check sql-error
     */
    protected function check_sql_error() {
        $sErr = $this->oDb->get_error_message();
        if($sErr != '') {
            throw new Exception('<h2>Error! ' . $sErr . '</h2><pre>' . $this->sCurrentQuery . '</pre>');
        }
    } // function check_sql_error

    /**
     * Get table list
     */
    protected function get_table_list($sShow = '') {
        $aTableList = $this->oDb->get_col('SHOW TABLES');
        asort($aTableList);
        if ($sShow) {
            echo '<p>' . $sShow . ':</p><pre>';
            print_r($aTableList);
            echo '</pre>';
            flush();
        }
        return $aTableList;
    } // function get_table_list

    /**
     * Clear Foreign keys
     */
    protected function clear_fk($aTableList) {
        foreach ($aTableList as $sTable) {
            $aCT = $this->oDb->get_row('SHOW CREATE TABLE `' . $sTable . '`');
            if (preg_match_all('/CONSTRAINT\s+\`(.+?)\`\s+FOREIGN KEY\s+/i', $aCT['Create Table'], $aKeys)) {
                foreach ($aKeys[1] as $sKey) {
                    $this->execute('ALTER TABLE `' . $sTable . '` DROP FOREIGN KEY `' . $sKey . '`');
                }
            }
        }
        $this->oDb->commit();
    } // function clear_fk

} // class \fan\app\__tools\main\scenario
?>