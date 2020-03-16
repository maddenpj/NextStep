<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Service\SponsorService;

require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$connstr = "pgsql:host=localhost;dbname=nextstep";
$pdo = new \PDO($connstr);
$ss = new SponsorService($pdo);

$app->get('/', function (Request $request, Response $response, $args) use ($ss) {
    $res = '<pre>'. print_r($ss->fetchAll(), true) . '</pre>';
    $response->getBody()->write($res);
    return $response;
});

$app->run();


// Show all information, defaults to INFO_ALL
// phpinfo();


?>
