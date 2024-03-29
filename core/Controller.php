<?php

namespace core;

abstract class Controller
{
    protected $route_params = [];
    protected $wrapperfile = 'Wrapper.twig';
    protected $adminvars = [];
    protected \Twig\Environment $loader;
    protected \Twig\TemplateWrapper $template;
    protected \Twig\TemplateWrapper $wrapper;
    protected $template_vars = [];

    public function __construct(array $route_params)
    {
        $loader = new \Twig\Loader\FilesystemLoader("../app/Views/");
        $this->route_params = $route_params;
        $this->loader = new \Twig\Environment($loader, [
            'cache' => false
        ]);
    }

    /**
     * Loading template
     * @param string $page template path and filename
     * @param array $vars template variables
     */
    public function loadTemplate(string $page, array $vars):void
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
     */
    public function loadEmailTemplate(string $page, array $vars):string
    {
        $this->template = $this->loader->load($page);
        return $this->template->render($vars);
    }

    public function __call(string $name, array $args)
    {
        $method = $name . 'Action';
        if (method_exists($this, $method)) {
            $this->before();
            call_user_func_array([$this, $method], $args);
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    protected function before()
    {
    }
}
