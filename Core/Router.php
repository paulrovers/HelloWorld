<?php

namespace Core;

use App\Controllers\Error404;
use Exception;

class Router
{
    /**
     * Associative array of routes (the routing table)
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     */
    protected $params = [];

    /**
     * Parameters from the matched route
     */
    protected $newpath = [];

    /**
     * Parameters from the matched route
     */
    protected $capturegroups = [];

    /**
     * Add a route to the routing table
     */
    public function add(string $route, array $params = []):void
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z0-9]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z0-9]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Get all the routes from the routing table
     */
    public function getRoutes():array
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     */
    public function match(string $url):bool
    {

        foreach ($this->routes as $route => $params) {

            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the currently matched parameters
     */
    public function getParams():array
    {
        return $this->params;
    }

    /**
     * search for capturegroups and place them in newpath if needed
     */
    public function addCapturegroups():string
    {
        if(count($this->capturegroups) > 0){
            foreach($this->capturegroups as $key => $value){
                $this->newpath = preg_replace("/{".$key."}/",$value,$this->newpath);
            }
        }
        return $this->newpath;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     * @return error404|mixed
     * @throws Exception
     */
    public function dispatch(string $url)
    {

        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {

                $controller_object = new $controller($this->params);

                $action = $this->params['action'];

                if (is_callable([$controller_object, $action])) {
                    $controller_object->$action();
                    return $controller_object;
                } else {
                    throw new \Exception("Method $action (in controller $controller) not found");
                }
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            //return error page
            $error_params = [
                'controller' => 'Error404',
                'action' => 'index'
            ];
            $controller_object = new error404($error_params);
            $controller_object->index();
            return $controller_object;
        }
    }

    /**
     * remove query string variables from url
     */
    protected function removeQueryStringVariables(string $url):string
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     */
    protected function getNamespace():string
    {
        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
