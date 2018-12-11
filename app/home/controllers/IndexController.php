<?php
namespace App\Home\Controllers;

use Common\BaseController;

class IndexController extends BaseController
{

    public function indexAction()
    {
        $this->view->name = 'phalcon-framwork';
    }

    public function smartyAction()
    {
        $this->view->title = 'test smarty';
    }
}