<?php

namespace app\controllers;

use core\Controller;

class contact extends Controller
{
    public $strTitle = 'Contact';
    public $strDescription = 'Description';
    public $strPageurl = '';
    public $h1Title = 'Contact';

    public function indexAction()
    {
        if (isset($this->route_params['name'])) {
            $page = (string)$this->route_params['name'];
        } else {
            $page = '';
        }
        $this->strPageurl = $_ENV['APP_URL'] . "/" . $page . "/";

        //load default template variables
        $array = [];

        //load template based on name used
        $this->loadTemplate('contact.twig', $array);
    }


}


