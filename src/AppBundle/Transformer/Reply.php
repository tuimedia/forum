<?php
namespace AppBundle\Transformer;

use AppBundle\Entity;
use League\Fractal;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Reply extends Fractal\TransformerAbstract
{
    use ContainerAwareTrait;

    protected $availableIncludes = [
        'post',
    ];

    protected $defaultIncludes = [];

    public function transform($entity)
    {
        if (!$entity instanceof Entity\Reply) {
            throw new \InvalidArgumentException('Expected a Reply entity, but got something else.');
        }

        $response = [
            'id' => $entity->getId(),
            'created' => $entity->getCreated()->format('c'),
            'updated' => $entity->getCreated()->format('c'),
            'content' => $entity->getContent(),
            // 'score' => intval($entity->getScore()),
            'createdBy' => $entity->getUserId(),
        ];

        return $response;
    }

    public function includePost($entity)
    {
        $entity = $entity->getPost();

        if (!$entity) {
            return;
        }

        return $this->item($entity, $this->container->get('transformer_post'), 'post');
    }
}
