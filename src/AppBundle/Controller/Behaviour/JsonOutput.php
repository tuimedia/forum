<?php
namespace AppBundle\Controller\Behaviour;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait JsonOutput
{
    protected function generateJson($data, Request $request, $statusCode = JsonResponse::HTTP_OK, $recursionLimit = 3)
    {
        $manager = $this->get('fractal');
        $manager->setRecursionLimit($recursionLimit);

        if ($request->query->has('include')) {
            $manager->parseIncludes($request->query->get('include'));
        }

        return new JsonResponse($manager->createData($data)->toArray(), $statusCode);
    }

    protected function generateErrorResponse($message, $statusCode = 422, $type = 'errorMessage')
    {
        return new JsonResponse([
            'data' => [
                'status' => 'error',
                'message' => $message,
            ],
            'meta' => ['type' => $type],
        ], $statusCode);
    }
}
