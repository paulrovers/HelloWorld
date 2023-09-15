<?php
/**
 * Route examples
 * $router->add('' , ['controller' => 'Homepage' , 'action' => 'index']);
 * $router->add('email-activate/{code:[a-zA-Z0-9]+}/', ['controller' => 'Emailactivate', 'action' => 'index']);
 * $router->add('{name:[a-zA-Z0-9-]+}[a-zA-Z0-9/_-]+' , ['controller' => 'Default' , 'action' => 'index']);
 */

#main pages
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('contact[/]*', ['controller' => 'Contact', 'action' => 'index']);
$router->add('login[/]*', ['controller' => 'Login', 'action' => 'index']);
$router->add('error[/]*', ['controller' => 'Error404', 'action' => 'index']);

#admin
$router->add('admin/home/', ['controller' => 'Admin\Home', 'action' => 'index']);
$router->add('admin/secret/', ['controller' => 'Admin\Secret', 'action' => 'index']);
$router->add('admin/logout/', ['controller' => 'Login', 'action' => 'logout']);