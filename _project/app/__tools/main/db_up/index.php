<?php namespace app\__tools\main\db_up;
/**
 * index block
 * @version 1.0
 */
class index extends \project\block\form\usual
{
    public function init()
    {
        require_once(dirname(__FILE__) . "/scenario.php");
        
        $this->aFieldMeta["scenario"]["data"] = array();

        $aScList = array();
        $sDirPath = \bootstrap::parsePath($this->getMeta("scenario_dir"));
        if (is_dir($sDirPath)) {
            $oDir = dir($sDirPath);
            while (($sEntry = $oDir->read()) !== false) {
                if (substr($sEntry, -4) == ".txt") {
                    $sKey = substr($sEntry, 0, -4);
                    $oSc = new scenario($sDirPath . $sEntry);
                    list($k, $v) = $oSc->get_next();
                    $aScList[$sKey] = $k == "description" ? $v : $sKey;
                }
            }
            $oDir->close();
        }
        if ($aScList) {
            asort($aScList);
            foreach ($aScList as $k => $v) {
                $this->aFieldMeta["scenario"]["data"][] = array("value" => $k, "text" => $v);
            }

            $this->parseForm();
            $this->setTemplateVar("isCorrect", 1);
        } else {
            $sRealDirPath = realpath($sDirPath);
            $this->setTemplateVar("sDirPath", $sRealDirPath ? $sRealDirPath : $this->getMeta("scenario_dir"));
        }
    }
} // class \app\__tools\main\db_up\index
?>