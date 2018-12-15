<?php
/**
 * @desc 处理错误类
 */
namespace App\Home\Controllers;

use Phalcon\Mvc\Controller;

class ErrorController extends Controller
{

    public function error404Action()
    {
        echo '404';
        exit();
    }
}