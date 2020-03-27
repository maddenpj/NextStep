<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Model\Geo;
use NextStep\Service\SponsorService;

require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$connstr = "pgsql:host=localhost;dbname=nextstep";
$pdo = new \PDO($connstr);
$ss = new SponsorService($pdo);

$app->get('/', function (Request $request, Response $response, $args) use ($ss) {
    $sponsors = $ss->fetchAll();
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($sponsors, JSON_PRETTY_PRINT));
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

$app->run();


// Show all information, defaults to INFO_ALL
// phpinfo();


?>
