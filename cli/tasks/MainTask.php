<?php
use Common\BaseTask;

class MainTask extends BaseTask
{

    public function mainAction()
    {
        echo "This is the cli main task and the main action" . PHP_EOL;
        var_dump($this->dispatcher->getParams());
    }
}