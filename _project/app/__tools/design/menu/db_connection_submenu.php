<?php namespace fan\app\__tools\design;
/**
 * db_connection_submenu block for tools
 * @version 05.02.001 (10.03.2014)
 */
class db_connection_submenu extends \fan\project\block\common\simple
{
    /**
     * @var array Menu
     */
    protected $aMenu = array();

    /**
     * @var string Current element
     */
    protected $sCurrent = null;

    /**
     * Init block
     */
    public function init()
    {
        $oReq = $this->getRequest();
        $sMainKey = implode('/', $oReq->getAll('M'));
        @list($this->sCurrent) = $oReq->getAll('A');

        $aConf = service('config')->get('database');
        foreach ($aConf['DATABASE'] as $k => $v) {
            $this->aMenu[$k] = array(
                'url'  => $this->oTab->getURI('~/' . $sMainKey . '/' . $k . '.html', 'link', null, null),
                'name' => 'DB <b>' . $k . '</b> (<i>' . $v['DATABASE'] . '</i>)',
            );
        }

        $this->view->aMenu    = $this->aMenu;
        $this->view->sCurrent = $this->sCurrent;
    }

    /**
     * Get current name
     * @return string
     */
    public function getCurrentName()
    {
        return @$this->aMenu[$this->sCurrent]['name'];
    }

} // class \fan\app\__tools\design\db_connection_submenu
?>