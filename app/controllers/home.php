<?php

namespace app\controllers;

use core\Controller;

class home extends Controller
{
    public $strTitle = 'Home';
    public $strDescription = 'Description';
    public $strPageurl = '';
    public $h1Title = 'Home';

    public function indexAction()
    {
        if (isset($this->route_params['name'])) {
            $page = (string)$this->route_params['name'];
        } else {
            $page = '';
        }
        $this->strPageurl = $_ENV['APP_URL'] . "/" . $page . "/";

        //load default template variables
        $array = [
            'test' => 'test',
            'testvar' => 'inhoud van testvar'
        ];

        //load template based on name used
        $this->loadTemplate('home.twig', $array);
    }


}


