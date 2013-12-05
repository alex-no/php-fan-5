<?php namespace app\__tools\main\db_up;
/**
 * Scenario processing block
 * @version 1.0
 */
class scenario_processing extends \project\block\common\simple
{
    /**
     * @var string Source meta file
     */
    protected $sScenarioFile = null;

    /**
     *
     */
    public function init()
    {
        if (!role('tools_access')) {
            return;
        }
        
        require_once(dirname(__FILE__) . '/scenario.php');

        $this->sScenarioFile = service('request')->get('scenario', 'GP', null);
        if ($this->sScenarioFile) {
            $this->sScenarioFile = \bootstrap::parsePath($this->getMeta('scenario_dir') . $this->sScenarioFile . '.txt');
            if (!file_exists($this->sScenarioFile)) {
                $this->sScenarioFile = null;
            }
        }
        if ($this->sScenarioFile) {
            $this->oTab->setOutputtingMethod($this, 'perform_db');
            $this->setTemplateVar('bIsCorrect', 1);
        } else {
            $this->setTemplateVar('bIsCorrect', 0);
        }
    }

    /**
     *
     */
    public function perform_db()
    {
        $aCode = explode('<!-- Repl -->', $this->oTab->getTabCode());
        echo $aCode[0];

        $oSc = new scenario($this->sScenarioFile, \bootstrap::parsePath($this->getMeta('dump_dir')));
        $oSc->parse_scenario();
        echo '<div id="finish"><h4 class="' . ($oSc->isSuccess() ? 'success' : 'error') . '">Process is finished ' . ($oSc->isSuccess() ? 'successfully' : 'with error') . '. Click <a href="about:blank">here</a> for clear result.</h4></div>';

        echo $aCode[1];
    }
} // class \app\__tools\main\db_up\scenario_processing
?>