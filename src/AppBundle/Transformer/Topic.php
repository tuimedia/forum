<?php
namespace AppBundle\Transformer;

use AppBundle\Entity;
use League\Fractal;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Topic extends Fractal\TransformerAbstract
{
    use ContainerAwareTrait;

    protected $availableIncludes = [
        'children',
        'parent',
        'posts',
    ];

    protected $defaultIncludes = [];

    public function transform($entity)
    {
        if (!$entity instanceof Entity\Topic) {
            throw new \InvalidArgumentException('Expected a Topic entity, but got something else.');
        }

        $response = [
            'id' => $entity->getId(),
            'created' => $entity->getCreated()->format('c'),
            'updated' => $entity->getCreated()->format('c'),
            'namespace' => $entity->getNamespace(),
            'title' => $entity->getTitle(),
            'externalReference' => $entity->getExternalReference(),
            'createdBy' => $entity->getUserId(),
            'parentId' => $entity->getParent() ? $entity->getParent()->getId() : null,
        ];

        return $response;
    }

    public function includePosts($entity)
    {
        $entities = $entity->getPosts();

        return $this->collection($entities, $this->container->get('transformer_post'), 'posts');
    }

    public function includeChildren($entity)
    {
        $entities = $entity->getChildren();

        return $this->collection($entities, $this->container->get('transformer_topic'), 'topics');
    }

    public function includeParent($entity)
    {
        $entity = $entity->getParent();

        if (!$entity) {
            return;
        }

        return $this->item($entity, $this->container->get('transformer_topic'), 'topic');
    }
}
