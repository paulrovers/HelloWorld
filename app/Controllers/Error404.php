<?php

namespace app\Controllers;

use core\Controller;

class Error404 extends Controller
{
    public $strTitle = 'Page not found';
    public $strDescription = 'Page not found';
    public $strPageurl = '';
    public $h1Title = 'Page not found';

    public function indexAction()
    {
        header("HTTP/1.1 404 Not Found");
        $this->strPageurl = $_ENV['APP_URL'].'/error/';

        //load default template variables
        $array = [];

        //load template based on name used
        $this->loadTemplate('Error404.twig', $array);
    }

}


