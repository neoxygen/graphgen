<?php

namespace Neoxygen\Graphgen\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse;

class WebController
{
    public function home(Application $application, Request $request)
    {
        return $application['twig']->render('base.html.twig');
    }

    public function transformPattern(Application $application, Request $request)
    {
        $parser = $application['parser'];
        $processor = $application['processor'];
        $pattern = $request->request->get('pattern');

        $parser->parseCypher($pattern);
        $schema = $parser->getSchema();

        $processor->process($schema);
        $graphJson = $processor->getGraphJson();

        $response = new JsonResponse();
        $response->setData($graphJson);

        return $response;

    }
}