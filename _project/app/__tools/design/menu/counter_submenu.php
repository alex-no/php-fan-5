<?php namespace fan\app\__tools\design;
/**
 * counter_submenu block for tools
 * @version 05.02.001 (10.03.2014)
 */
class counter_submenu extends \fan\project\block\base
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

        $this->view->sCurrent = $sCurrent;
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

} // class \fan\app\__tools\design\counter_submenu
?>