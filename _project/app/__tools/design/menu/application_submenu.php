<?php namespace app\__tools\design\menu;
/**
 * application_submenu block for tools
 * @version 1.0
 */
class application_submenu extends \project\block\base
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
        $oTab = $this->oTab;
        $sMainKey = implode('/', $oTab->getMainRequest());
        @list($this->sCurrent) = $oTab->getAddRequest();

        $aConf = service('config')->get('application');
        foreach ($aConf['APPLICATIONS'] as $k => $v) {
            $this->aMenu[$k] = array(
                'url'  => $this->oTab->getURI('/' . $sMainKey . '/' . $k . '.html', 'link', null, null),
                'name' => 'APP <b>' . $k . '</b>',
            );
        }

        $this->setTemplateVar('aMenu', $this->aMenu);
        $this->setTemplateVar('sCurrent', $this->sCurrent);
    }

    /**
     * Get current name
     * @return string
     */
    public function getCurrentName()
    {
        return @$this->aMenu[$this->sCurrent]['name'];
    }

} // class \app\__tools\design\menu\application_submenu
?>