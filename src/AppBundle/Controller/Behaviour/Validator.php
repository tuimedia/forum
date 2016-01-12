<?php
namespace AppBundle\Controller\Behaviour;

use Symfony\Component\HttpFoundation\JsonResponse;

trait Validator
{
    protected function validate($entity)
    {
        $violations = $this->get('validator')->validate($entity);

        if (count($violations)) {
            $messages = array_map(function ($item) {
                return $item->getMessage();
            }, iterator_to_array($violations));

            return new JsonResponse([
                'data' => ['status' => 'error', 'message' => implode(PHP_EOL, $messages)],
            ], 422);
        }
    }
}
