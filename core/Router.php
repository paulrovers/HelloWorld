<?php

namespace core;

use app\controllers\error404;
use Exception;
use InvalidArgumentException;

class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $redirectRoutes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $params = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $newpath = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $capturegroups = [];

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    public function add(string $route, array $params = [])
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
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param string $newpath  The redirect path
     *
     * @return void
     */
    public function addRedirect(string $route, string $newpath)
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z0-9]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z0-9]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->redirectRoutes[$route] = $newpath;
    }


    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    public function match(string $url)
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
     * Match the route to the redirectroutes in the routing table, setting the $newpath
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */

    public function redirectMatch(string $url)
    {
        foreach ($this->redirectRoutes as $route => $newpath) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $this->capturegroups[$key] = $match;
                    }
                }
                $this->newpath = $newpath;
                return true;
            }
        }

        return false;
    }


    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * search for capturegroups and place them in newpath if needed
     *
     * @return string
     */
    public function addCapturegroups()
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
     * @param string $url
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
                'controller' => 'error404',
                'action' => 'index'
            ];
            $controller_object = new error404($error_params);
            $controller_object->index();
            return $controller_object;
        }
    }

    /**
     * Redirect the route
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public function redirect(string $url)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->redirectMatch($url)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$_ENV['APP_URL'].$this->addCapturegroups());
            exit;
        }
    }

    /**
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables(string $url)
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
     *
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'app\controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
