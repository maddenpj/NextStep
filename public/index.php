<?php

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use NextStep\Model\Geo;
use NextStep\Service\SponsorService;
use NextStep\Service\LikeService;
use NextStep\Service\DislikeService;

require __DIR__ . '/../vendor/autoload.php';


/////////////////////////////////////////
// Stuff to be moved into better config
/////////////////////////////////////////
$containerBuilder = new ContainerBuilder();


$containerBuilder->addDefinitions([
    'config' => [
        'json.settings' => JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        'sql.connection' => "pgsql:host=localhost;dbname=nextstep"
    ],
    \PDO::class => function (Container $c) {
        return new \PDO($c->get('config')['sql.connection'], null, null, array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ));
    },
    'SponsorService' => function (Container $c) {
        return new SponsorService($c->get(\PDO::class));
    },
]);


////////////////////
// App Setup Stuff
////////////////////

$container = $containerBuilder->build();
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
    // Should pull out into a json encoder service
    $jsonSettings = $this->get('config')['json.settings'];
    $json = json_encode($sponsors, $jsonSettings);
    $response->getBody()->write($json);
    return $response->withHeader('Content-Type', 'application/json');
});

// Should use groups eventually?
$app->get('/user/{id}', function (Request $req, Response $res, $args) {
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    $jsonSettings = $this->get('config')['json.settings'];
    $json = json_encode($sponsor, $jsonSettings);
    $res->getBody()->write($json);
    return $res->withHeader('Content-Type', 'application/json');
});

$app->get('/user/{id}/likes', function (Request $req, Response $res, $args) {
    $jsonSettings = $this->get('config')['json.settings'];
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    $likes = $this->get(LikeService::class)->fetch($sponsor);
    $json = json_encode($likes, $jsonSettings);
    $res->getBody()->write($json);
    return $res->withHeader('Content-Type', 'application/json');
});

$app->post('/user/{id}/likes', function (Request $req, Response $res, $args) {
    $data = $req->getParsedBody();
    $ss = $this->get('SponsorService');
    $sponsor = $ss->fetch($args['id']);
    if (isset($data['liked'])) {
        $lid = $this->get(LikeService::class)->insert($sponsor, $data['liked']);
        $res->getBody()->write(var_export($lid, true));
    }
    return $res->withHeader('Content-Type', 'application/json');
});

$app->group('/user/{id}/dislikes', function ($group) {

    $group->get('', function (Request $req, Response $res, $args) {
        $jsonSettings = $this->get('config')['json.settings'];
        $ss = $this->get('SponsorService');
        $sponsor = $ss->fetch($args['id']);
        $likes = $this->get(DislikeService::class)->fetch($sponsor);
        $json = json_encode($likes, $jsonSettings);
        $res->getBody()->write($json);
        return $res->withHeader('Content-Type', 'application/json');
    });

    $group->post('', function (Request $req, Response $res, $args) {
        $data = $req->getParsedBody();
        $ss = $this->get('SponsorService');
        $sponsor = $ss->fetch($args['id']);
        if (isset($data['disliked'])) {
            $lid = $this->get(DislikeService::class)->insert($sponsor, $data['disliked']);
            $res->getBody()->write(var_export($lid, true));
        }
        return $res;
    });

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


$app->get('/distance', function (Request $req, Response $res, $args) {
    // Default lat/long for testing
    $g = new Geo(33.6560, -117.8994);
    $qs = $req->getQueryParams();

    if (isset($qs['lat']) && isset($qs['long'])) {
        $g = new Geo($qs['lat'], $qs['long']);
    }

    $ss = $this->get('SponsorService');
    $sponsors = $ss->fetchByDistance($g, $qs['radius']);

    $jsonSettings = $this->get('config')['json.settings'];
    $json = json_encode($sponsors, $jsonSettings);
    $res->getBody()->write($json);
    return $res->withHeader('Content-Type', 'application/json');
});

$app->get('/phpinfo', function ($req, $res, $args) {
    phpinfo();
    return $res;
});

$app->run();

?>
