<?php namespace app\__tools\design\menu;
/**
 * main_menu block for tools
 * @version 1.0
 */
class main_menu extends \project\block\base
{
    /**
     * Init block
     */
    public function init()
    {
        $aMainRequest = service('request')->getAll('M');
        $this->view->sCurrent = $aMainRequest[0];
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

} // class \app\__tools\design\menu\main_menu
?>