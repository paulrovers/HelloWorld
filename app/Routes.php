<?php
/**
 * Route examples
 * $router->add('' , ['controller' => 'Homepage' , 'action' => 'index']);
 * $router->add('email-activate/{code:[a-zA-Z0-9]+}/', ['controller' => 'Emailactivate', 'action' => 'index']);
 * $router->add('{name:[a-zA-Z0-9-]+}[a-zA-Z0-9/_-]+' , ['controller' => 'Default' , 'action' => 'index']);
 */

#main pages
$router->add('', ['controller' => 'home', 'action' => 'index']);
$router->add('contact[/]*', ['controller' => 'contact', 'action' => 'index']);
$router->add('login[/]*', ['controller' => 'login', 'action' => 'index']);
$router->add('error[/]*', ['controller' => 'error404', 'action' => 'index']);

#admin
$router->add('admin/home/', ['controller' => 'admin\adminhome', 'action' => 'index']);
$router->add('admin/secret/', ['controller' => 'admin\secret', 'action' => 'index']);
$router->add('admin/logout/', ['controller' => 'login', 'action' => 'logout']);