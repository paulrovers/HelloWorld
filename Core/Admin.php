<?php

namespace Core;

class Admin extends Controller{

    //set meta tags for all admin pages
    public $strTitle = 'Control Panel';
    public $strDescription = 'Control panel';
    public $strPageurl = 'admin';
    public $h1Title = 'Login';

    protected function before()
    {
        //if user is not authenticated, redirect to login page
        $this->requireLogin();

        //set wrapper file to admin wrapper
        $this->wrapperfile = 'Admin/Wrapper.twig';

        //set shared admin vars
        $this->adminvars = [];
    }

    /**
     * Login the user, user array contains, email and password
     * @param array $user
     */
    public static function login(array $user)
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
    }

    /**
     * If you're not logged in, you get redirected to the frontpage
     * this method is called on all pages where login is mandatory
     */
    protected function requireLogin()
    {
        if(!isset($_SESSION['user_id'])){
            $this->logout();
            header("Location: /login/");
            exit;
        }
    }

    /**
     * Logout function
     */
    public static function logout()
    {
        // Unset all of the session variables
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }


}