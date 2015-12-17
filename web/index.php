<?php

require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

use Neoxygen\Neogen\Neogen;
use Neoxygen\Graphgen\Service\Neo4jClient,
    Neoxygen\Graphgen\Statistics\StatisticService,
    Neoxygen\Graphgen\Service\ConverterService,
    Neoxygen\Graphgen\Service\UrlShortenerService,
    Neoxygen\Graphgen\Service\GraphGistService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['root_dir'] = sys_get_temp_dir();
$app['debug'] = true;
$app['neogen'] = new Neogen();
$neo4jService = new Neo4jClient();
$app['neo4j'] = $neo4jService;
$app['shortUrl'] = new UrlShortenerService();
$app['stats'] = new StatisticService($app['neo4j'], $app['shortUrl']);
$app['converter'] = new ConverterService($app['neogen']);
$app['ggist'] = new GraphGistService();

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Views',
));
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../logs/graphgen.log'
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Key');
    $response->headers->set('Access-Control-Allow-Methods', '*');
});

$app->match("{url}", function($url) use ($app) { return "OK"; })->assert('url', '.*')->method("OPTIONS");

$app->get('/', 'Neoxygen\\Graphgen\\Controller\\WebController::home')
    ->bind('home');

$app->get('/documentation/{part}', 'Neoxygen\\Graphgen\\Controller\\WebController::docAction')
    ->value('part', 'introduction')
    ->bind('doc');

$app->get('api/documentation/{part}', 'Neoxygen\\Graphgen\\Controller\\WebController::apiDocAction')
    ->value('part', 'introduction')
    ->bind('doc');

$app->get('/support-graphgen', 'Neoxygen\\Graphgen\\Controller\\WebController::supportAction')
    ->bind('support');

$app->post('/api/pattern/transform', 'Neoxygen\\Graphgen\\Controller\\WebController::transformPattern')
    ->bind('api_pattern_transform');

$app->post('/api/pattern/precalculate', 'Neoxygen\\Graphgen\\Controller\\WebController::precalculateAction')
    ->bind('api_pattern_precalculate');

$app->post('/api/console/create', 'Neoxygen\\Graphgen\\Controller\\WebController::createConsoleAction')
    ->bind('api_console_create');

$app->post('/api/export/graphjson', 'Neoxygen\\Graphgen\\Controller\\WebController::exportToGraphJson')
    ->bind('api_export_graphjson');

$app->post('/api/export/cypher', 'Neoxygen\\Graphgen\\Controller\\WebController::exportToCypher')
    ->bind('api_export_cypher');

$app->post('/api/export/populate', 'Neoxygen\\Graphgen\\Controller\\WebController::getPopulateQueries')
    ->bind('api_export_populate');

$app->post('/api/populate/external', 'Neoxygen\\Graphgen\\Controller\\WebController::populateExternal')
    ->bind('api_populate_external');

$app->post('/api/graphgist/create', 'Neoxygen\\Graphgen\\Controller\\WebController::createGraphGist')
    ->bind('api_graphgist_create');

$app->get('/show/gist/{data}', 'Neoxygen\\Graphgen\\Controller\\WebController::newWindow')
    ->bind('api_showgist');

$app->run();
