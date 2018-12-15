<?php
namespace App\Home\Controllers;

use Common\BaseController;

class IndexController extends BaseController
{

    public function indexAction()
    {
        echo 111;
        exit();
        $this->view->name = 'phalcon-framwork';
    }

    public function smartyAction()
    {
        echo 222;
        exit();
        $this->view->title = 'test smarty';
    }
}