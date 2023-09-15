<?php

namespace App\Controllers;

use App\Models\Users;
use Core\Admin;
use Core\Controller;

class Login extends Controller
{
    public $strTitle = 'Login';
    public $strDescription = 'Description';
    public $strPageurl = '';
    public $h1Title = 'Login';

    public function indexAction()
    {
        //check if form has been submitted
        if(isset($_POST['email']) && isset($_POST['password'])){

            $user = new users();

            //check if credentials are correct
            if($user->verify_login($_POST['email'],$_POST['password'])){
                //login user
                Admin::login($user->get_user_by_email($_POST['email']));
                //redirect to admin page
                header("Location: /admin/home/");
            }else {
                //load default template variables
                $array = [
                    'error' => 'The used credentials are unknown to us.'
                ];
            }
        }

        if (isset($this->route_params['name'])) {
            $page = (string)$this->route_params['name'];
        } else {
            $page = '';
        }
        $this->strPageurl = $_ENV['APP_URL'] . "/login/";

        //create empty array if not set yet
        if(!isset($array)){$array = [];}

        //load template based on name used
        $this->loadTemplate('Login.twig', $array);
    }

    public function logoutAction()
    {
        Admin::logout();
        header("Location: /login/");
        exit;
    }

}


