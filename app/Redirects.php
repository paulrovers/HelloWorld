<?php
/**
 * Adding redirects with regular expressions
 * Examples:
 * $router->addRedirect('page1', 'page2/'); //redirects domain/page1 to domain.com/page2/
 * $router->addRedirect('page1[/]*', 'page2/'); //redirects (domain/page1 or domain/page1/) to domain.com/page2/
 * $router->addRedirect('{name:[a-z]+}/harry/', 'namepages/{name}/harry/');
 * //redirects domain/(a lowercase string with capturegroup name)/harry/ to domain/namepages/(capturegroup name)/harry/
 */

$router->addRedirect('example[/]*', '');

#### add / ad the end of each path ####
$router->addRedirect('{path:[a-zA-Z0-9-]+}(?!/)', '/{path}/');
$router->addRedirect('{path:[a-zA-Z0-9-]+}/{path2:[a-zA-Z0-9-]+}(?!/)', '/{path}/{path2}/');
$router->addRedirect('{path:[a-zA-Z0-9-]+}/{path2:[a-zA-Z0-9-]+}/{path3:[a-zA-Z0-9-]+}(?!/)', '/{path}/{path2}/{path3}/');

