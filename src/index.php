<?php
/**
 * Created by PhpStorm.
 * User: thiago
 * Date: 31/03/18
 * Time: 11:53
 */

require_once __DIR__ . '/bootstrap.php';

/**
 * Routes
 */
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', \MyWonderland\Controller\HomeController::class . ':index');
    $r->addRoute('GET', '/auth', \MyWonderland\Controller\SpotifyAuthController::class . ':auth');
    $r->addRoute('GET', '/callback', \MyWonderland\Controller\SpotifyAuthController::class . ':callback');
    $r->addRoute('GET', '/logout', \MyWonderland\Controller\SpotifyAuthController::class . ':logout');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$queryStringVars = [];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $queryString = substr($uri, $pos+1);
    parse_str($queryString, $queryStringVars);
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        print "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        print "405 Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        list($class, $method) = explode(":", $handler, 2);
        $container = new \MyWonderland\Container();
        call_user_func_array(array($container->build($class), $method), $vars + $queryStringVars);
        break;
}