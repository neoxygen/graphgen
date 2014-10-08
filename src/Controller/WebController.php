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
        $current = 0;
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            $fetch = apc_fetch('graphgen_generated');
            if (null !== $fetch) {
                $current = $fetch;
            }
        }

        return $application['twig']->render('base.html.twig', array('current' => $current));
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

    private function increment()
    {
        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            $current = apc_fetch('graphgen_generated');
            apc_store('graphgen_generated', $current++);
        }
    }
}