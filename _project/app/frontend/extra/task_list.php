<?php namespace fan\app\frontend\extra;
/**
 * task_list class
 * @version 05.02.001 (10.03.2014)
 */
class task_list extends \fan\project\block\common\simple
{
    /**
     * List of tasks
     * @var array
     */
    protected $aTasks = array();

    /**
     * Method for redefine in child class
     * Method if run after construct operation
     */
    protected function _postCreate()
    {
        $this->aTasks = adduceToArray($this->aMeta['tasks']['ru']);
    } // function _postCreate

    /**
     * Add New Task
     * @param string $sTask
     * @return \fan\app\frontend\extra\task_list
     */
    public function addTask($sTask)
    {
        $this->aTasks[] = $sTask;
        return $this;
    } // function addTask

    /**
     * Init block data
     */
    public function init()
    {
        $this->view->tasks = $this->aTasks;
    } // function init
} // class \fan\app\frontend\extra\task_list
?>