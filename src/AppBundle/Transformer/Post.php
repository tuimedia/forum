<?php
namespace AppBundle\Transformer;

use AppBundle\Entity;
use League\Fractal;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Post extends Fractal\TransformerAbstract
{
    use ContainerAwareTrait;

    protected $availableIncludes = [
        'replies',
        'topic',
    ];

    protected $defaultIncludes = [];

    public function transform($entity)
    {
        if (!$entity instanceof Entity\Post) {
            throw new \InvalidArgumentException('Expected a Post entity, but got something else.');
        }

        $response = [
            'id' => $entity->getId(),
            'created' => $entity->getCreated()->format('c'),
            'updated' => $entity->getCreated()->format('c'),
            'content' => $entity->getContent(),
            'score' => intval($entity->getScore()),
            'isSticky' => $entity->getIsSticky() ? true : false,
            'createdBy' => $entity->getUserId(),
            'topicId' => $entity->getTopic()->getId(),
        ];

        return $response;
    }

    public function includeReplies($entity)
    {
        $entities = $entity->getReplies();

        return $this->collection($entities, $this->container->get('transformer_reply'), 'replies');
    }

    public function includeTopic($entity)
    {
        $entity = $entity->getTopic();

        if (!$entity) {
            return;
        }

        return $this->item($entity, $this->container->get('transformer_topic'), 'topic');
    }
}
