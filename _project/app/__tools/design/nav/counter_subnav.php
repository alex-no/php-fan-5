<?php namespace fan\app\__tools\design;
/**
 * counter_subnav block for tools
 * @version 05.02.005 (12.02.2015)
 */
class counter_subnav extends \fan\project\block\common\simple
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

        $aMainRequest = $this->getRequest()->getAll('M');;
        $this->sMainKey = $aMainRequest[0];
        $sCurrent = array_val($aMainRequest, 1);

        $this->view->sCurrent = $sCurrent;
    }

    /**
     * Get Nav Url
     * @param string $sKey
     * @param string $sAddUrl
     * @return string
     */
    public function getNavUrl($sKey, $sAddUrl = '')
    {
        return $this->oTab->getURI('~/' . $this->sMainKey . '/' . $sKey . $sAddUrl . '.html', 'link', null, null);
    }

} // class \fan\app\__tools\design\counter_subnav
?>