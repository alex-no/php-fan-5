<?php namespace app\__tools\design\menu;
/**
 * counter_submenu block for tools
 * @version 1.0
 */
class counter_submenu extends \project\block\base
{

    /**
     * @var string Main key element
     */
    protected $sMainKey = null;
    /**
     * Init block
     */
    public function init()
    {
        $oTab = $this->oTab;

        $aMainRequest = $oTab->getMainRequest();
        $this->sMainKey = $aMainRequest[0];
        $sCurrent = @$aMainRequest[1];

        $this->setTemplateVar("sCurrent", $sCurrent);
    }

    /**
     * Get Menu Url
     * @param string $sKey
     * @param string $sAddUrl
     * @return string
     */
    public function getMenuUrl($sKey, $sAddUrl = "")
    {
        return $this->oTab->getURI("/" . $this->sMainKey . "/" . $sKey . $sAddUrl . ".html", "link", null, null);
    }

} // class \app\__tools\design\menu\counter_submenu
?>