<?php

namespace app\controllers\admin;



class adminhome extends \core\Admin
{
    public function indexAction()
    {

        $this->h1Title = 'Admin Home';

        $array = [
            'id' => 1
        ];

        $this->loadTemplate('admin/adminhome.twig', $array);
    }

}