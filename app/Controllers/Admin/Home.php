<?php

namespace app\Controllers\Admin;

class Home extends \core\Admin
{
    public function indexAction()
    {

        $this->h1Title = 'Admin Home';

        $array = [
            'id' => 1
        ];

        $this->loadTemplate('Admin/Home.twig', $array);
    }

}