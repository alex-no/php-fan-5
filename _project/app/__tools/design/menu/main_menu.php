<?php namespace fan\app\__tools\design;
/**
 * main_menu block for tools
 * @version 05.02.001 (10.03.2014)
 */
class main_menu extends \fan\project\block\common\simple
{
    /**
     * Init block
     */
    public function init()
    {
        $sMainRequest = $this->getRequest()->get(0, 'M');
        $this->view->sCurrent .= $sMainRequest;
    }

    /**
     * Get Menu Url
     * @param string $sKey
     * @param string $sAddUrl
     * @return string
     */
    public function getMenuUrl($sKey, $sAddUrl = '')
    {
        return $this->oTab->getURI('~/' . $sKey . $sAddUrl . '.html', 'link', null, null);
    }

} // class \fan\app\__tools\design\main_menu
?>