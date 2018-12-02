<?php
namespace App\Home\Controllers;

use App\Common\BaseController;

class IndexController extends BaseController
{

    public function indexAction()
    {
        $this->view->name = 'phalcon-framwork';
    }
}