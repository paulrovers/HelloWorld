<?php

namespace app\Controllers;

use core\Controller;

class Contact extends Controller
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

        $array = [];
        //load template based on name used
        $this->loadTemplate('Contact.twig', $array);
    }
}




