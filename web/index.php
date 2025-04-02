<?php

require __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\JsonResponse;

$container = new \Pimple\Container();

$container['db'] = function () {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = [
        'dbname' => 'hellofresh',
        'user' => 'hellofresh',
        'password' => 'hellofresh',
        'host' => 'postgres',
        'port' => 5432,
        'driver' => 'pdo_pgsql'
    ];
    return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
};

$container['recipe_repository'] = function ($c) {
    return new \App\Repository\RecipeRepository($c['db']);
};
$container['rating_repository'] = function ($c) {
    return new \App\Repository\RatingRepository($c['db']);
};

$container['authentication_service'] = function () {
    return new \App\Service\AuthenticationService();
};

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->get('/recipes', ['App\Controller\RecipeController', 'list']);
    $r->post('/recipes', ['App\Controller\RecipeController', 'create']);
    $r->get('/recipes/{id:\d+}', ['App\Controller\RecipeController', 'get']);
    $r->put('/recipes/{id:\d+}', ['App\Controller\RecipeController', 'update']);
    $r->delete('/recipes/{id:\d+}', ['App\Controller\RecipeController', 'delete']);
    $r->post('/recipes/{id:\d+}/rating', ['App\Controller\RecipeController', 'rate']);
});

$request = ServerRequestFactory::fromGlobals();
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        $response = new JsonResponse(['error' => 'Not Found'], 404);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $response = new JsonResponse(['error' => 'Method Not Allowed'], 405);
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $controller = new $handler[0]($container);
        $response = call_user_func([$controller, $handler[1]], $request, $vars);
        break;
    default:
        $response = new JsonResponse(['error' => 'Internal Server Error'], 500);
}

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();