<?php

namespace core;

abstract class Controller
{
    protected $route_params = [];
    protected $wrapperfile = 'wrapper.twig';
    protected $adminvars = [];

    public function __construct(array $route_params)
    {
        $loader = new \Twig\Loader\FilesystemLoader("../app/views/");
        $this->route_params = $route_params;
        $this->loader = new \Twig\Environment($loader, [
            'cache' => false
        ]);
    }

    /**
     * Loading template
     * @param string $page template path and filename
     * @param array $vars template variables
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function loadTemplate(string $page, array $vars)
    {
        //create twig subpage
        $this->template = $this->loader->load($page);
        $subpage = $this->template->render($vars);

        //create twig wrapper & include subpage
        $this->wrapper = $this->loader->load($this->wrapperfile);
        $this->template_vars = [
            'index' => $_ENV['APP_INDEX'],
            'site_url' => $_ENV['APP_URL'],
            'title' => $this->strTitle,
            'description' => $this->strDescription,
            'pageurl' => $this->strPageurl,
            'h1_title' => $this->h1Title,
            'page' => $page,
            'subpage' => $subpage,
            'adminvars' => $this->adminvars
        ];

        echo $this->wrapper->render($this->template_vars);

    }

    /**
     * Load email template
     * @param string $page
     * @param array $vars
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function loadEmailTemplate(string $page, array $vars){
        $this->template = $this->loader->load($page);
        return $this->template->render($vars);
    }

    public function __destruct()
    {
    }

    /**
     * @param $name
     * @param $args
     * @throws \Exception
     */
    public function __call(string $name, array $args)
    {
        $method = $name . 'Action';
        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * filter incoming data
     */
    protected function before()
    {
        if(isset($_SESSION)) {
            foreach ($_SESSION as $name => $value) {
                $_SESSION[$name] = filter_var($_SESSION[$name]);
            }
        }
    }

    protected function after()
    {

    }

}
