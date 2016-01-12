<?php
namespace AppBundle\Transformer;

use AppBundle\Entity;
use League\Fractal;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Rating extends Fractal\TransformerAbstract
{
    use ContainerAwareTrait;

    protected $availableIncludes = [
        'post',
    ];

    protected $defaultIncludes = [];

    public function transform($entity)
    {
        if (!$entity instanceof Entity\PostRating) {
            throw new \InvalidArgumentException('Expected a PostRating entity, but got something else.');
        }

        $response = [
            'id' => $entity->getId(),
            'created' => $entity->getCreated()->format('c'),
            'updated' => $entity->getCreated()->format('c'),
            'score' => intval($entity->getScore()),
            'postId' => $entity->getPost()->getId(),
            'createdBy' => $entity->getUserId(),
        ];

        return $response;
    }

    public function includePost($entity)
    {
        $entity = $entity->getPost();

        return $this->item($entity, $this->container->get('transformer_post'), 'post');
    }
}
