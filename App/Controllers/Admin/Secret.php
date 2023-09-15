<?php

namespace App\Controllers\Admin;

class Secret extends \Core\Admin
{
    public function indexAction()
    {
        $this->h1Title = 'Secret page :)';

        $array = [
            'examplevar' => 'awesome data'
        ];

        $this->loadTemplate('Admin/Secret.twig', $array);
    }
}