<?php

require_once __DIR__.'/../vendor/autoload.php';

use Neoxygen\Neogen\Neogen;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['root_dir'] = sys_get_temp_dir();
$app['debug'] = true;
$app['neogen'] = new Neogen();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->get('/', 'Neoxygen\\Graphgen\\Controller\\WebController::home')
    ->bind('home');

$app->post('/api/pattern/process', 'Neoxygen\\Graphgen\\Controller\\WebController::transformPattern')
    ->bind('api_pattern_transform');

$app->post('/api/export/graphjson', 'Neoxygen\\Graphgen\\Controller\\WebController::exportToGraphJson')
    ->bind('api_export_graphjson');

$app->post('/api/export/cypher', 'Neoxygen\\Graphgen\\Controller\\WebController::exportToCypher')
    ->bind('api_export_cypher');

$app->post('/api/export/populate', 'Neoxygen\\Graphgen\\Controller\\WebController::getPopulateQueries')
    ->bind('api_export_populate');
$app->run();