<?php

namespace Neoxygen\Graphgen\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse,
    Neoxygen\Neogen\Exception\SchemaException;

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
        $response = new JsonResponse();

        try {
            $parser->parseCypher($pattern);
            $schema = $parser->getSchema();

            $processor->process($schema);
            $graphJson = $processor->getGraphJson();
            $response->setData($graphJson);
            $response->setStatusCode(200);
        } catch (SchemaException $e) {
            $data = array(
                'error' => array(
                    'code' => 320,
                    'message' => $e->getMessage()
                )
            );
            $response->setData($data);
            $response->setStatusCode(320);
        }

        return $response;

    }
}