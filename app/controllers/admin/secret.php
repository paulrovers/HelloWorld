<?php

namespace app\controllers\admin;

class secret extends \core\Admin
{
    public function indexAction()
    {
        $this->h1Title = 'Secret page :)';

        $array = [
            'examplevar' => 'awesome data'
        ];

        $this->loadTemplate('admin/secret.twig', $array);
    }
}