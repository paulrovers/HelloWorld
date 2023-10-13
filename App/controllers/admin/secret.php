<?php

namespace app\Controllers\Admin;

class Secret extends \core\Admin
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