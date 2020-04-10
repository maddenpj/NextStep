<?php

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Model\Geo;
use NextStep\Service\SponsorService;

require __DIR__ . '/../vendor/autoload.php';


/////////////////////////////////////////
// Stuff to be moved into better config
/////////////////////////////////////////
$containerBuilder = new ContainerBuilder();


$containerBuilder->addDefinitions([
    'config' => [
        'json.settings' => JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ]
]);

$container = $containerBuilder->build();
$connstr = "pgsql:host=localhost;dbname=nextstep";
$pdo = new \PDO($connstr);
// Shouldn't container auto-wire this
$container->set('SponsorService', function () use ($pdo) {
    return new SponsorService($pdo);
});

////////////////////
// App Setup Stuff
////////////////////
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

//////////////////
// Routes
/////////////////
$app->get('/', function (Request $request, Response $response, $args) {
    $ss = $this->get('SponsorService');
    $sponsors = $ss->fetchAll();
    $response = $response->withHeader('Content-Type', 'application/json');
    // Should pull out into a json encoder service
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

$app->get('/user/{id}/likes', function (Request $req, Response $res, $args) {
    $res = $res->withHeader('Content-Type', 'application/json');
    $jsonSettings = $this->get('config')['json.settings'];
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    $likes = $ss->getLikes($sponsor);
    $json = json_encode($likes, $jsonSettings);
    $res->getBody()->write($json);
    return $res;
});

$app->post('/user/{id}/likes', function (Request $req, Response $res, $args) {
    $data = $req->getParsedBody();
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    if (isset($data['liked'])) {
        $lid = $ss->addLike($sponsor, $data['liked']);
        $res->getBody()->write(var_export($lid, true));
    }
    return $res;
});

$app->post('/user/create', function (Request $req, Response $res, $args) {
    $data = $req->getParsedBody();
    // $res->getBody()->write(var_export($data, true));
    $ss = $this->get('SponsorService');
    echo '<pre>';
    print_r($data);
    $id = $ss->insert($data);
    $sponsor = $ss->fetch($id);
    if (!empty($data['image'])) {
        $sponsor = $ss->insertImage($sponsor, $data['image']);
    }

    print_r($sponsor);
    echo '</pre>';
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

?>
