<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Model\Geo;
use NextStep\Service\SponsorService;

require __DIR__ . '/../vendor/autoload.php';


/////////////////////////////////////////
// Stuff to be moved into better config
/////////////////////////////////////////
$connstr = "pgsql:host=localhost;dbname=nextstep";
// $jsonSettings = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;


////////////////////
// App Setup Stuff
////////////////////
$app = AppFactory::create();

$pdo = new \PDO($connstr);
$ss = new SponsorService($pdo);

$app->get('/', function (Request $request, Response $response, $args) use ($ss) {
    $sponsors = $ss->fetchAll();
    $response = $response->withHeader('Content-Type', 'application/json');
    $jsonSettings = JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    $json = json_encode($sponsors, $jsonSettings);
    $response->getBody()->write($json);
    return $response;
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
