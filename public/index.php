<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Model\Geo;
use NextStep\Service\SponsorService;

require __DIR__ . '/../vendor/autoload.php';


/////////////////////////////////////////
// Stuff to be moved into better config
/////////////////////////////////////////
$container = new Container();
// $jsonSettings = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

$connstr = "pgsql:host=localhost;dbname=nextstep";
$pdo = new \PDO($connstr);
// $ss = new SponsorService($pdo);

$container->set('config', function () {
    return [
        'json.settings' => JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ];
});

$container->set('SponsorService', function () use ($pdo) {
    return new SponsorService($pdo);
});

////////////////////
// App Setup Stuff
////////////////////


AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $ss = $this->get('SponsorService');
    $sponsors = $ss->fetchAll();
    $response = $response->withHeader('Content-Type', 'application/json');
    $jsonSettings = $this->get('config')['json.settings'];
    $json = json_encode($sponsors, $jsonSettings);
    $response->getBody()->write($json);
    return $response;
});

// Should use groups eventually?
$app->get('/user/{id}', function (Request $req, Response $res, $args) {
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    $res = $res->withHeader('Content-Type', 'application/json');
    $jsonSettings = $this->get('config')['json.settings'];
    $json = json_encode($sponsor, $jsonSettings);
    $res->getBody()->write($json);
    return $res;
});

$app->get('/distance', function (Request $request, Response $response, $args) use ($ss) {

    echo '<pre>';
    $g = new Geo(33.6560, -117.8994);
    $qs = $request->getQueryParams();

    if (isset($qs['lat']) && isset($qs['long'])) {
        $g = new Geo($qs['lat'], $qs['long']);
    }

    $sponsors = $ss->fetchByDistance($g, $qs['radius']);

    foreach ($sponsors as &$s) {
        $s['daysSober'] = $s['sponsor']->getDaysSober();
    }


    print_r($request->getQueryParams());
    print_r($sponsors);

    echo '</pre>';
    // $response->getBody()->write($res);
    return $response;
});

$app->get('/phpinfo', function ($req, $res, $args) {
    phpinfo();
    return $res;
});

$app->run();


// Show all information, defaults to INFO_ALL
// phpinfo();


?>
