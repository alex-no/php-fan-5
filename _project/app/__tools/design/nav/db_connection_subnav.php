<?php namespace fan\app\__tools\design;
/**
 * db_connection_subnav block for tools
 * @version 05.02.006 (20.04.2015)
 */
class db_connection_subnav extends \fan\project\block\common\simple
{
    /**
     * @var array Nav
     */
    protected $aNav = array();

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
        $this->sCurrent = $oReq->get(0, 'A');

        $aConf = service('config')->get('database');
        foreach ($aConf['DATABASE'] as $k => $v) {
            $this->aNav[$k] = array(
                'url'  => $this->oTab->getURI('~/' . $sMainKey . '/' . $k . '.html', 'link', null, null),
                'name' => 'DB <b>' . $k . '</b> (<i>' . $v['DATABASE'] . '</i>)',
            );
        }

        $this->view->aNav    = $this->aNav;
        $this->view->sCurrent = $this->sCurrent;
    }

    /**
     * Get current name
     * @return string
     */
    public function getCurrentName()
    {
        return @$this->aNav[$this->sCurrent]['name'];
    }

} // class \fan\app\__tools\design\db_connection_subnav
?>