<?php
#main pages
$router->add('', ['controller' => 'home', 'action' => 'index']);
$router->add('contact[/]*', ['controller' => 'contact', 'action' => 'index']);
$router->add('login[/]*', ['controller' => 'login', 'action' => 'index']);
$router->add('error[/]*', ['controller' => 'error404', 'action' => 'index']);

#admin
$router->add('admin/home/', ['controller' => 'admin\adminhome', 'action' => 'index']);
$router->add('admin/secret/', ['controller' => 'admin\secret', 'action' => 'index']);
$router->add('admin/logout/', ['controller' => 'login', 'action' => 'logout']);